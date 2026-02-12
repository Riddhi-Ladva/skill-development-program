import requests
from bs4 import BeautifulSoup
import os
import json
import time
import re
import argparse
import glob
from urllib.parse import urljoin, urlparse

class KiloCrawler:
    def __init__(self):
        self.base_url = "https://www.kilointernational.com"
        self.headers = {
            "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) "
                          "AppleWebKit/537.36 (KHTML, like Gecko) "
                          "Chrome/115.0.0.0 Safari/537.36"
        }
        self.session = requests.Session()
        self.session.headers.update(self.headers)
        
        # Define output bases matching previous specific scripts, but now under 'output/'
        self.dirs = {
            'nav': os.path.join('output', 'category'),
            'listing': os.path.join('output', 'part'),
            'pdp': os.path.join('output', 'part1')
        }

    def setup_directories(self, step='all'):
        """Creates required folder structure based on step."""
        print(f"[INIT] Setting up directories for {step}...")
        
        # Nav directories
        if step in ['nav', 'all']:
            os.makedirs(os.path.join(self.dirs['nav'], 'tables'), exist_ok=True)
            os.makedirs(os.path.join(self.dirs['nav'], 'markdowns'), exist_ok=True)
        
        # Listing directories
        if step in ['listing', 'all']:
            for sub in ['images', 'tables', 'markdowns']:
                os.makedirs(os.path.join(self.dirs['listing'], sub), exist_ok=True)
            
        # PDP directories
        if step in ['pdp', 'all']:
            for sub in ['images', 'block_diagrams', 'documentation', 'design_resources', 'markdowns', 'tables']:
                os.makedirs(os.path.join(self.dirs['pdp'], sub), exist_ok=True)

    def clean_filename(self, text):
        """Sanitizes strings for filenames."""
        text = text.lower()
        text = re.sub(r'[^a-z0-9]+', '_', text)
        return text.strip('_')[:50]

    def download_file(self, url, folder_path, filename_prefix="", ext_override=None):
        """Generic file downloader."""
        if not url: return None
        
        try:
            parsed = urlparse(url)
            filename = os.path.basename(parsed.path)
            if not filename or '.' not in filename:
                filename = f"file_{int(time.time())}"
            
            if ext_override:
                name, _ = os.path.splitext(filename)
                filename = f"{name}.{ext_override}"
                
            clean_name = f"{filename_prefix}_{filename}" if filename_prefix else filename
            clean_name = re.sub(r'[^\w\-\.]', '_', clean_name)
            
            filepath = os.path.join(folder_path, clean_name)
            
            # Return relative path for JSON
            rel_path = os.path.relpath(filepath, start=os.path.dirname(os.path.dirname(folder_path)))
            # rel_path logic is tricky if folder_path varies. 
            # Let's return path relative to the specific "part" or "part1" root.
            # actually scrape_listing returned "images/filename.jpg". 
            # I will return the filename and let caller format relative path.
            
            if os.path.exists(filepath):
                return clean_name

            response = self.session.get(url, timeout=15)
            if response.status_code == 200:
                with open(filepath, 'wb') as f:
                    f.write(response.content)
                return clean_name
        except Exception as e:
            print(f"[WARN] Failed to download {url}: {e}")
        return None
    def load_json(self, path):
        """Helper to load JSON data."""
        try:
            with open(path, 'r') as f:
                return json.load(f)
        except Exception as e:
            print(f"[WARN] Could not load {path}: {e}")
            return None

    def get_categories_from_disk(self):
        """Loads categories from Step 1 output."""
        path = os.path.join(self.dirs['nav'], 'tables', 'categories.json')
        print(f"[LOAD] Loading categories from {path}")
        return self.load_json(path)

    def get_products_from_disk(self):
        """Loads all products from Step 2 output files."""
        all_products = []
        path_pattern = os.path.join(self.dirs['listing'], 'tables', '*.json')
        print(f"[LOAD] Loading products from {path_pattern}")
        files = glob.glob(path_pattern)
        
        for fpath in files:
            data = self.load_json(fpath)
            if data and 'products' in data:
                all_products.extend(data['products'])
        
        print(f"[LOAD] Loaded {len(all_products)} products.")
        return all_products

    # ==========================================
    # STEP 1: NAVIGATION
    # ==========================================
    def step_1_scrape_nav(self):
        print("\n=== STEP 1: SCRAPING NAVIGATION ===")
        url = self.base_url
        categories = []
        exclude_names = ["NOT IN USE", "IN USE", "DISTRIBUTOR", "DISTRIBUTORS"]

        try:
            response = self.session.get(url, timeout=15)
            soup = BeautifulSoup(response.text, 'html.parser')
            nav = soup.find('nav', id='mainNavigation')
            
            if nav:
                items = nav.find_all('div', class_=['collection', 'folder'])
                for item in items:
                    a_tag = item.find('a')
                    if not a_tag: continue
                    
                    name = a_tag.get_text(strip=True)
                    if name.upper() in exclude_names: continue
                    
                    full_url = urljoin(url, a_tag['href'])
                    
                    # Logic for subcategories if needed (from scrape_nav.py)
                    sub_cats = []
                    if 'folder' in item.get('class', []):
                         subnav = item.find('div', class_='subnav')
                         if subnav:
                             subs = subnav.find_all('div', class_='collection')
                             for sub in subs:
                                 sub_a = sub.find('a')
                                 if sub_a:
                                     sub_cats.append({
                                         "name": sub_a.get_text(strip=True),
                                         "url": urljoin(url, sub_a['href'])
                                     })
                    
                    categories.append({
                        "name": name,
                        "url": full_url,
                        "subcategories": sub_cats
                    })
                    print(f"[NAV] Found category: {name}")

            # Save Nav Data
            with open(os.path.join(self.dirs['nav'], 'tables', 'categories.json'), 'w') as f:
                json.dump(categories, f, indent=4)
            
            # Save Nav Markdown
            with open(os.path.join(self.dirs['nav'], 'markdowns', 'categories.md'), 'w') as f:
                f.write("# Discovered Categories\n\n")
                for c in categories:
                    f.write(f"- [{c['name']}]({c['url']})\n")

        except Exception as e:
            print(f"[ERROR] Step 1 Failed: {e}")
        
        return categories

    # ==========================================
    # STEP 2: LISTING (PART)
    # ==========================================
    def step_2_scrape_listings(self, categories):
        print("\n=== STEP 2: SCRAPING LISTINGS ===")
        all_products = []

        for cat in categories:
            cat_name = cat['name']
            cat_url = cat['url']
            print(f"[LISTING] Processing: {cat_name}...")
            
            products_in_cat = []
            try:
                # Politeness
                time.sleep(1)
                
                response = self.session.get(cat_url, timeout=15)
                soup = BeautifulSoup(response.text, 'html.parser')
                items = soup.select('#productList .product')
                
                for item in items:
                    title_tag = item.select_one('.product-title')
                    if not title_tag: continue
                    
                    name = title_tag.get_text(strip=True)
                    
                    # Fix: Handle logic where item IS the link vs contains link
                    link_tag = item if item.name == 'a' else item.find('a', href=True)
                    prod_url = urljoin(cat_url, link_tag['href']) if link_tag else ""
                    
                    # Image
                    img_tag = item.select_one('.product-image img')
                    img_url = img_tag.get('data-src') or img_tag.get('src') if img_tag else ""
                    
                    # Download Thumbnail
                    img_filename = self.download_file(
                        img_url, 
                        os.path.join(self.dirs['listing'], 'images'),
                        filename_prefix=f"{self.clean_filename(cat_name)}_{self.clean_filename(name)}"
                    )
                    
                    # Path relative to output/part/ for JSON
                    local_img_rel = f"images/{img_filename}" if img_filename else ""
                    
                    product_obj = {
                        "category": cat_name,
                        "name": name,
                        "url": prod_url,
                        "thumbnail": local_img_rel,
                        "short_description": ""
                    }
                    products_in_cat.append(product_obj)
                    all_products.append(product_obj)

                # Save Category JSON & Markdown
                safe_cat = self.clean_filename(cat_name)
                
                # JSON
                with open(os.path.join(self.dirs['listing'], 'tables', f"{safe_cat}.json"), 'w') as f:
                    json.dump({"category": cat_name, "products": products_in_cat}, f, indent=4)
                
                # Markdown
                with open(os.path.join(self.dirs['listing'], 'markdowns', f"{safe_cat}.md"), 'w') as f:
                    f.write(f"# {cat_name}\n\n")
                    for p in products_in_cat:
                        f.write(f"## {p['name']}\n")
                        f.write(f"**URL:** {p['url']}\n")
                        if p['thumbnail']:
                            f.write(f"**Thumbnail:** {p['thumbnail']}\n")
                        f.write("---\n")
                        
            except Exception as e:
                print(f"[ERROR] listing fetch for {cat_name}: {e}")

        return all_products

    # ==========================================
    # STEP 3: PDP (PART 1)
    # ==========================================
    def step_3_scrape_pdps(self, products):
        print("\n=== STEP 3: SCRAPING DETAIL PAGES ===")
        
        # Group products by category to save aggregated JSONs
        categorized_data = {}

        for i, prod in enumerate(products):
            if not prod['url']: continue
            
            print(f"[PDP] Scraping {prod['name']} ({i+1}/{len(products)})...")
            time.sleep(1.5) # Politeness
            
            try:
                response = self.session.get(prod['url'], timeout=15)
                if response.status_code != 200: continue
                
                soup = BeautifulSoup(response.text, 'html.parser')
                cat_name = prod['category']
                safe_name = self.clean_filename(prod['name'])
                
                # 1. Description
                desc_tag = soup.select_one('.product-excerpt')
                description = desc_tag.get_text(separator=' ', strip=True) if desc_tag else ""
                
                # 2. Specs
                specs = [li.get_text(strip=True) for li in soup.select('.product-description ul li')]
                
                # 3. Images (High Res)
                image_urls = []
                seen_imgs = set()
                
                # Strategy: Look for all possible image containers
                # A. Slideshow slides (often duplicates of thumbs, but high res)
                for img in soup.select('#productSlideshow .slide img'):
                    src = img.get('data-src') or img.get('src')
                    if src:
                        full_url = urljoin(self.base_url, src)
                        if full_url not in seen_imgs:
                            image_urls.append(full_url)
                            seen_imgs.add(full_url)

                # B. Product Gallery Thumbnails (often link to high res or are high res)
                # Squarespace often uses .product-gallery-thumbnails or similar
                # Added #productThumbnails (ID) and .sqs-gallery (general gallery blocks)
                for img in soup.select('.product-gallery-thumbnails img, #productThumbnails img, .sqs-gallery .slide img'):
                    src = img.get('data-src') or img.get('src')
                    if src:
                        # Sometimes thumbs are resized, try to find original if pattern matches
                        # For now, just get what's there, as 'data-src' usually points to full
                        full_url = urljoin(self.base_url, src)
                        if full_url not in seen_imgs:
                            image_urls.append(full_url)
                            seen_imgs.add(full_url)

                # C. Main visual if no slideshow found yet
                if not image_urls:
                    main_imgs = soup.select('.product-image img, #productMainImage img')
                    for img in main_imgs:
                        src = img.get('data-src') or img.get('src')
                        if src:
                            full_url = urljoin(self.base_url, src)
                            if full_url not in seen_imgs:
                                image_urls.append(full_url)
                                seen_imgs.add(full_url)
                                
                print(f"[DEBUG] Found {len(image_urls)} images for {prod['name']}")
                # Fallback
                if not image_urls:
                    main_img = soup.select_one('.product-image img')
                    if main_img:
                        src = main_img.get('data-src') or main_img.get('src')
                        image_urls.append(urljoin(self.base_url, src))

                # 4. Docs/Resources
                docs = []
                design_res = []
                for a in soup.select('.product-description a[href]'):
                    href = urljoin(self.base_url, a['href'])
                    txt = a.get_text(strip=True) or "File"
                    
                    if href.lower().endswith('.pdf'):
                        docs.append({'title': txt, 'url': href})
                    elif href.lower().endswith(('.step', '.igs', '.dwg', '.dxf', '.zip')):
                        design_res.append({'title': txt, 'url': href})

                # Downloads
                local_images = []
                for idx, img_url in enumerate(image_urls):
                    fname = self.download_file(img_url, os.path.join(self.dirs['pdp'], 'images'), f"{safe_name}_{idx+1}", "jpg")
                    if fname: local_images.append(f"images/{fname}")

                local_docs = []
                for d in docs:
                    fname = self.download_file(d['url'], os.path.join(self.dirs['pdp'], 'documentation'), f"{safe_name}_{self.clean_filename(d['title'])}")
                    if fname: local_docs.append(f"documentation/{fname}")
                    
                local_designs = []
                for d in design_res:
                    fname = self.download_file(d['url'], os.path.join(self.dirs['pdp'], 'design_resources'), f"{safe_name}_{self.clean_filename(d['title'])}")
                    if fname: local_designs.append(f"design_resources/{fname}")

                pdp_data = {
                    "category": cat_name,
                    "product_name": prod['name'],
                    "product_url": prod['url'],
                    "description": description,
                    "specifications": specs,
                    "technical_details": "",
                    "images": local_images,
                    "block_diagrams": [],
                    "documentation": local_docs,
                    "design_resources": local_designs
                }
                
                # Accumulate
                if cat_name not in categorized_data:
                    categorized_data[cat_name] = []
                categorized_data[cat_name].append(pdp_data)
                
                # Save Individual Markdown
                md_path = os.path.join(self.dirs['pdp'], 'markdowns', f"{safe_name}.md")
                with open(md_path, 'w', encoding='utf-8') as f:
                    f.write(f"# {prod['name']}\n")
                    f.write(f"**URL:** {prod['url']}\n\n")
                    f.write(f"## Description\n{description}\n\n")
                    f.write("## Images\n")
                    for img in local_images:
                        f.write(f"![Image](../{img})\n")
                
            except Exception as e:
                print(f"[ERROR] PDP Scrape {prod['name']}: {e}")

        # Save Aggregated JSONs
        print("\n[SAVING] Saving Aggregated PDP Data...")
        for cat_name, data in categorized_data.items():
            safe_cat = self.clean_filename(cat_name)
            path = os.path.join(self.dirs['pdp'], 'tables', f"{safe_cat}_detailed.json")
            with open(path, 'w', encoding='utf-8') as f:
                json.dump({"category": cat_name, "products": data}, f, indent=4)
            print(f"[INFO] Saved {path}")

    def run(self):
        parser = argparse.ArgumentParser(description="KiloInternational Crawler")
        parser.add_argument('--step', choices=['nav', 'listing', 'pdp', 'all'], default='all', help="Which step to run")
        parser.add_argument('--url', help="Single URL to scrape (auto-detects type)")
        parser.add_argument('--out', help="Output directory (optional override)")
        args = parser.parse_args()

        print(f"Starting KiloInternational Crawler...")

        # If --url is provided, we auto-detect mode
        if args.url:
            print(f"[AUTO] Detecting page type for: {args.url}")
            try:
                response = self.session.get(args.url, timeout=15)
                soup = BeautifulSoup(response.text, 'html.parser')
                
                # Check for Listing
                if soup.select_one('#productList'):
                    print("[DETECTED] Category Listing Page")
                    args.step = 'listing'
                    # Construct dummy category
                    path_parts = [p for p in args.url.split('/') if p]
                    cat_name = path_parts[-1].replace('-', ' ').title() if path_parts else "Unknown"
                    manual_category = [{"name": cat_name, "url": args.url, "subcategories": []}]
                
                # Check for PDP
                elif soup.select_one('.product-gallery-thumbnails, #productSlideshow, .product-price, .sqs-add-to-cart-button'):
                    print("[DETECTED] Product Detail Page (PDP)")
                    args.step = 'pdp'
                    # Construct dummy product
                    path_parts = [p for p in args.url.split('/') if p]
                    prod_name = path_parts[-1].replace('-', ' ').title() if path_parts else "Unknown"
                    # Try to guess category from URL structure (e.g. /dials/400-series -> Dials)
                    cat_name = path_parts[-2].replace('-', ' ').title() if len(path_parts) > 1 else "Uncategorized"
                    manual_product = [{"name": prod_name, "url": args.url, "category": cat_name, "thumbnail": ""}]
                
                # Check for Nav (Home)
                elif soup.select_one('#mainNavigation'):
                    print("[DETECTED] Navigation/Home Page")
                    args.step = 'nav'
                
                else:
                    print("[ERROR] Could not detect page type. Defaulting to 'all'.")
            except Exception as e:
                print(f"[ERROR] Detection failed: {e}")
                return

        if args.out:
            # Update output paths based on mode
            if args.step == 'all':
                 self.dirs = {
                    'nav': os.path.join(args.out, 'category'),
                    'listing': os.path.join(args.out, 'part'),
                    'pdp': os.path.join(args.out, 'part1')
                }
            elif args.step == 'nav':
                self.dirs['nav'] = args.out
            elif args.step == 'listing':
                self.dirs['listing'] = args.out
            elif args.step == 'pdp':
                self.dirs['pdp'] = args.out

        print(f"Starting KiloInternational Crawler (Mode: {args.step.upper()})...")
        self.setup_directories(args.step)
        
        # Step 1: Nav
        if args.step in ['nav', 'all']:
            categories = self.step_1_scrape_nav()
            if args.step == 'nav': return

        # Step 2: Listings
        if args.step in ['listing', 'all']:
            # If skipping step 1, load from disk OR use manual if --url
            if args.step == 'listing':
                if args.url and 'manual_category' in locals():
                    categories = manual_category
                else:
                    categories = self.get_categories_from_disk()
            
            if not categories:
                print("[STOP] No categories available.")
                return

            products = self.step_2_scrape_listings(categories)
            if args.step == 'listing': return

        # Step 3: PDPs
        if args.step in ['pdp', 'all']:
            # If skipping step 2, load from disk OR use manual if --url
            if args.step == 'pdp':
                if args.url and 'manual_product' in locals():
                    products = manual_product
                else:
                    products = self.get_products_from_disk()
            
            if not products:
                print("[STOP] No products available.")
                return

            self.step_3_scrape_pdps(products)
        
        print("\n[DONE] Execution Finished.")

if __name__ == "__main__":
    KiloCrawler().run()
