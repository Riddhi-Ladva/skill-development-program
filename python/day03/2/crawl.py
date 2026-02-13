import requests
from bs4 import BeautifulSoup
import os
import json
import time
import re
import argparse
import sys
import logging
from urllib.parse import urljoin, urlparse
from markdownify import markdownify as md

# --- Configuration ---
HEADERS = {
    "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) "
                  "AppleWebKit/537.36 (KHTML, like Gecko) "
                  "Chrome/115.0.0.0 Safari/537.36"
}
TIMEOUT = 15

# --- Utility Functions ---
class ScraperUtils:
    @staticmethod
    def setup_logging():
        logging.basicConfig(
            level=logging.INFO,
            format='%(asctime)s [%(levelname)s] %(message)s',
            handlers=[logging.StreamHandler(sys.stdout)]
        )

    @staticmethod
    def clean_filename(text):
        """Sanitizes strings for safe filenames."""
        if not text: return "unknown"
        text = text.lower()
        text = re.sub(r'[^a-z0-9]+', '_', text)
        return text.strip('_')[:50]

    @staticmethod
    def get_soup(url):
        """Fetches URL and returns BeautifulSoup object with error handling."""
        try:
            response = requests.get(url, headers=HEADERS, timeout=TIMEOUT)
            response.raise_for_status()
            return BeautifulSoup(response.text, 'html.parser')
        except Exception as e:
            logging.error(f"Failed to fetch {url}: {e}")
            return None

    @staticmethod
    def download_file(url, folder_path, filename_prefix="", ext_override=None):
        """Robust file downloader."""
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
            if os.path.exists(filepath):
                return clean_name

            response = requests.get(url, headers=HEADERS, timeout=TIMEOUT)
            if response.status_code == 200:
                with open(filepath, 'wb') as f:
                    f.write(response.content)
                return clean_name
        except Exception as e:
            logging.warn(f"Failed to download {url}: {e}")
        return None

    @staticmethod
    def save_json(data, folder, filename):
        """Saves data to JSON file."""
        os.makedirs(folder, exist_ok=True)
        path = os.path.join(folder, filename)
        with open(path, 'w', encoding='utf-8') as f:
            json.dump(data, f, indent=4)
        ScraperUtils.update_metadata(folder, {"file": filename, "type": "json_data"})

    @staticmethod
    def save_markdown(content, folder, filename):
        """Saves content to Markdown file."""
        os.makedirs(folder, exist_ok=True)
        path = os.path.join(folder, filename)
        with open(path, 'w', encoding='utf-8') as f:
            f.write(content)
        ScraperUtils.update_metadata(folder, {"file": filename, "type": "markdown_content"})

    @staticmethod
    def init_metadata(folder):
        """Ensures metadata.json exists in the folder with an empty list if new."""
        os.makedirs(folder, exist_ok=True)
        meta_path = os.path.join(folder, 'metadata.json')
        if not os.path.exists(meta_path):
            with open(meta_path, 'w') as f:
                json.dump([], f, indent=4)

    @staticmethod
    def update_metadata(folder, entry):
        """Updates metadata.json in the folder."""
        meta_path = os.path.join(folder, 'metadata.json')
        data = []
        if os.path.exists(meta_path):
            try:
                with open(meta_path, 'r') as f:
                    data = json.load(f)
            except: pass
        data.append(entry)
        with open(meta_path, 'w') as f:
            json.dump(data, f, indent=4)

    @staticmethod
    def _html_to_str(html):
        if not html:
            return ""
        if isinstance(html, list):
            return " ".join(str(x) for x in html if x)
        if isinstance(html, (dict, int, float)):
            return str(html)
        return str(html)

    @staticmethod
    def clean_html_spaces(text: str) -> str:
        if not text:
            return ""
        return (
            text.replace("&nbsp;", " ")   # replace HTML non-breaking spaces
                .replace("\xa0", " ")     # replace unicode non-breaking spaces
                .replace("\u00a0", " ")   # extra safety
        )

    @staticmethod
    def write_overview_markdown(soup, div_selector, section_title=None, url=None):
        div = soup.select_one(div_selector)
        if not div:
            # Fallback to soup if selector not found, or maybe just return empty?
            # User code said: if not div: div = soup. 
            # But scanning whole soup might be too much. Let's follow user code.
            div = soup

        parsed = urlparse(url)
        base_url = f"{parsed.scheme}://{parsed.netloc}"

        for tag in div.select("a[href], img[src]"):
            if tag.name == "a" and tag["href"].startswith("/"):
                tag["href"] = (base_url.rstrip("/") if base_url else "") + tag["href"]
            elif tag.name == "img" and tag["src"].startswith("/"):
                tag["src"] = (base_url.rstrip("/") if base_url else "") + tag["src"]
        
        for btn in div.select("button[onclick]"):
            onclick = btn.get("onclick", "")
            m = re.search(r"location\.href\s*=\s*['\"]([^'\"]+)['\"]", onclick)
            if not m:
                continue

            href = m.group(1)

            # Make absolute if needed
            if href.startswith("/"):
                href = (base_url.rstrip("/") if base_url else "") + href

            # Create <a> tag
            a = soup.new_tag("a", href=href)

            # Preserve button text
            text = btn.get_text(strip=True)
            a.string = text if text else "Download"

            btn.replace_with(a)

        html_content = div.decode_contents().strip()
        if not html_content:
            return ""

        # Convert HTML -> Markdown
        markdown_text = md(html_content, heading_style="ATX")

        # Clean up and normalize whitespace
        markdown_text = ScraperUtils._html_to_str(markdown_text)
        markdown_text = ScraperUtils.clean_html_spaces(markdown_text)

        # Remove excessive blank lines (3+ -> 1)
        markdown_text = re.sub(r"\n{3,}", "\n\n", markdown_text.strip())

        # Trim leading/trailing spaces per line
        markdown_text = "\n".join(line.strip() for line in markdown_text.splitlines())

        # Add section title if provided
        if section_title:
            section_header = f"## {section_title}\n\n"
        else:
            section_header = ""

        return section_header + markdown_text.strip() + "\n"

# --- Main Engine ---
class KiloEngine:
    def __init__(self, url, output_dir=None):
        self.url = url
        self.session = requests.Session()
        self.session.headers.update(HEADERS)
        
        # Determine output folder name from URL slug if not provided
        if not output_dir:
            parsed = urlparse(url)
            slug = parsed.path.strip('/').split('/')[-1] or "home"
            self.base_output = os.path.join("output", ScraperUtils.clean_filename(slug))
        else:
            self.base_output = output_dir

    def run(self):
        logging.info(f"Starting crawl for {self.url}")
        soup = ScraperUtils.get_soup(self.url)
        if not soup:
            logging.error("Could not fetch page. Exiting.")
            return

        page_type = self.detect_page_type(soup)
        logging.info(f"Detected Page Type: {page_type}")

        if page_type == "excluded":
            logging.info("Skipping excluded page type (distributors/in-use). No folder created.")
            return

        if page_type == "category":
            self.handle_category(soup)
        elif page_type == "product":
            self.handle_product(soup)
        else:
            logging.error("Unknown page type. Cannot proceed.")

    def detect_page_type(self, soup):
        """Heuristic to detect Category Page vs PDP vs Excluded."""
        # 1. Check for Excluded Pages by URL or Title
        url_lower = self.url.lower()
        title = soup.title.string.lower() if soup.title and soup.title.string else ""
        
        excluded_keywords = ["distributors", "in-use", "in use", "buy-1", "new-gallery"]
        
        # Check URL
        if any(k in url_lower for k in excluded_keywords):
            return "excluded"
            
        # Check Title
        if "distributors" in title or "in use" in title:
            return "excluded"

        # 2. Check for Category Page (Product List)
        if soup.select_one('#productList') or soup.find('div', class_='product-list'):
            return "category"
            
        # 3. Check for PDP (Add to Cart / Price)
        if soup.select_one('.sqs-add-to-cart-button') or soup.select_one('.product-price'):
            return "product"
            
        # 4. Fallback URL Check for PDPs
        if "/knobs/" in self.url and len(self.url.split('/')) > 4: # e.g. /knobs/series/model
            return "product"
            
        return "category" # Default fallback

    def handle_category(self, soup):
        logging.info("Processing Category Page...")
        
        # Structure: output/slug/images, output/slug/tables, output/slug/markdowns
        dirs = {
            'images': os.path.join(self.base_output, 'images'),
            'tables': os.path.join(self.base_output, 'tables'),
            'markdowns': os.path.join(self.base_output, 'markdowns')
        }
        for d in dirs.values(): 
            os.makedirs(d, exist_ok=True)
            ScraperUtils.init_metadata(d)

        products = []
        # Try finding products
        items = soup.select('#productList .product, .product-list .product')
        parsed = urlparse(self.url)
        cat_name = parsed.path.strip('/').split('/')[-1]

        for item in items:
            title_tag = item.select_one('.product-title')
            if not title_tag: continue
            
            name = title_tag.get_text(strip=True)
            link_tag = item.find('a', href=True)
            if not link_tag:
                 # Check if the item itself is an 'a' tag (common in some Squarespace templates)
                 if item.name == 'a' and item.has_attr('href'):
                     link_tag = item
                 else:
                     logging.warning(f"Skipping product {name}: No link found.")
                     continue
            
            link = link_tag['href']
            abs_link = urljoin(self.url, link)
            
            # Image
            img_tag = item.select_one('img')
            img_url = img_tag.get('data-src') or img_tag.get('src') if img_tag else ""
            
            # Download Listing Thumb
            fname = ScraperUtils.download_file(
                img_url, dirs['images'], filename_prefix=ScraperUtils.clean_filename(name)
            )
            if fname:
                ScraperUtils.update_metadata(dirs['images'], {
                    "file": fname,
                    "url": img_url,
                    "product": name,
                    "type": "category_thumbnail"
                })
            
            products.append({
                "name": name,
                "url": abs_link,
                "thumbnail": f"images/{fname}" if fname else ""
            })

        # Save Data
        ScraperUtils.save_json({"category": cat_name, "products": products}, dirs['tables'], f"{cat_name}.json")
        
        # Save Markdown
        md_content = f"# {cat_name.title()}\n\n"
        for p in products:
            md_content += f"## {p['name']}\n**URL**: {p['url']}\n\n"
            if p['thumbnail']:
                md_content += f"![Thumb]({p['thumbnail']})\n\n"
        ScraperUtils.save_markdown(md_content, dirs['markdowns'], f"{cat_name}.md")
        
        logging.info(f"Saved {len(products)} products from category.")

    def handle_product(self, soup):
        logging.info("Processing Product Detail Page (PDP)...")
        
        # Structure: output/slug/{images, block_diagrams, etc.}
        pdp_dirs = [
            'images', 'block_diagrams', 'documentation', 'design_resources', 
            'software_tools', 'training', 'other', 'tables', 'markdowns'
        ]
        dirs = {d: os.path.join(self.base_output, d) for d in pdp_dirs}
        for d in dirs.values(): 
            os.makedirs(d, exist_ok=True)
            ScraperUtils.init_metadata(d)
        
        # Initialize block diag mapping
        bd_map_path = os.path.join(dirs['block_diagrams'], 'block_diagram_mappings.json')
        if not os.path.exists(bd_map_path):
             with open(bd_map_path, 'w') as f: json.dump([], f)

        # ... [Rest of the code until '4. Resources'] ...

        # 1. Product Details
        name_elem = soup.select_one('.product-title, #productName')
        prod_name = name_elem.text.strip() if name_elem else "Unknown Product"
        safe_name = ScraperUtils.clean_filename(prod_name)
        
        desc_elem = soup.select_one('.product-excerpt, .product-description, #productDescription')
        description = desc_elem.text.strip() if desc_elem else ""
        desc_html = str(desc_elem) if desc_elem else ""

        # 2. Images Analysis (High Res)
        image_urls = []
        seen_imgs = set()
        
        # Slideshow
        for img in soup.select('#productSlideshow .slide img'):
            src = img.get('data-src') or img.get('src')
            if src: image_urls.append(urljoin(self.url, src))

        # Thumbnails / Gallery
        for img in soup.select('.product-gallery-thumbnails img, #productThumbnails img, .sqs-gallery .slide img'):
            src = img.get('data-src') or img.get('src')
            if src: image_urls.append(urljoin(self.url, src))

        # Main Image Fallback
        if not image_urls:
             main = soup.select_one('.product-image img')
             if main: 
                 src = main.get('data-src') or main.get('src')
                 if src: image_urls.append(urljoin(self.url, src))

        # Deduplicate preserving order
        unique_urls = []
        for u in image_urls:
            if u not in seen_imgs:
                unique_urls.append(u)
                seen_imgs.add(u)
        
        # Download Main Images
        local_images = []
        for i, url in enumerate(unique_urls):
            fname = ScraperUtils.download_file(url, dirs['images'], f"{safe_name}_{i+1}", "jpg")
            if fname: 
                local_images.append(f"images/{fname}")
                ScraperUtils.update_metadata(dirs['images'], {"file": fname, "url": url, "type": "product_gallery"})

        # 3. Block Diagrams & Description Images (CRITICAL LOGIC PRESERVED)
        block_diagram_urls = []
        local_block_diagrams = []
        
        desc_area = soup.select_one('.product-description')
        if desc_area:
            logging.info("Scanning description for diagrams and lookups...")
            for img in desc_area.select('img'):
                src = img.get('data-src') or img.get('src')
                if not src: continue
                full_url = urljoin(self.url, src)
                
                # Heuristic: Diagrams are PNG/GIF
                is_diagram = any(ext in full_url.lower() for ext in ['.png', '.gif'])
                
                if is_diagram:
                    # It's a Block Diagram
                    if full_url not in [b['url'] for b in block_diagram_urls]:
                        classes = img.get('class', [])
                        class_str = " ".join(classes)
                        block_diagram_urls.append({'url': full_url, 'class': class_str})
                else:
                    # It's a Product Lookup (JPG) in description
                    if full_url not in seen_imgs:
                        idx = len(seen_imgs) + 1
                        fname = ScraperUtils.download_file(full_url, dirs['images'], f"{safe_name}_desc_{idx}", "jpg")
                        if fname:
                            local_images.append(f"images/{fname}")
                            ScraperUtils.update_metadata(dirs['images'], {"file": fname, "url": full_url, "type": "description_lookup"})
                            seen_imgs.add(full_url)

        # Download Block Diagrams
        for i, bd in enumerate(block_diagram_urls):
            ext = "png" if ".png" in bd['url'].lower() else "gif"
            fname = ScraperUtils.download_file(bd['url'], dirs['block_diagrams'], f"{safe_name}_diagram_{i+1}", ext)
            if fname:
                local_block_diagrams.append(f"block_diagrams/{fname}")
                # Add to mapping
                with open(bd_map_path, 'r+') as f:
                    data = json.load(f)
                    data.append({"file": fname, "type": "block_diagram", "product": prod_name, "class": bd['class']})
                    f.seek(0)
                    json.dump(data, f, indent=4)
                
                # Add to metadata.json
                ScraperUtils.update_metadata(dirs['block_diagrams'], {
                    "file": fname, 
                    "url": bd['url'], 
                    "type": "block_diagram"
                })

        # 4. Resources
        # Lists to hold filenames
        local_resources = {
            'documentation': [],
            'design_resources': [],
            'software_tools': [],
            'training': [],
            'other': []
        }

        # Scanning all links
        for a in soup.select('.product-description a[href]'):
            href = urljoin(self.url, a['href'])
            txt = (a.get_text(strip=True) or "File").lower()
            href_lower = href.lower()
            
            # Skip non-file links (heuristic)
            if not any(href_lower.endswith(ext) for ext in ['.pdf', '.zip', '.step', '.igs', '.dxf', '.dwg', '.stp', '.exe', '.msi', '.mp4', '.mov']):
                 # If it doesn't look like a file extension we know, skip OR put in other if it looks like a file download
                 # For now, let's stick to known extensions to avoid downloading HTML pages
                 continue

            category = 'other'
            
            # Software / Tools
            if any(k in txt for k in ['software', 'driver', 'firmware', 'tool']) or \
               any(href_lower.endswith(ext) for ext in ['.exe', '.msi', '.bin', '.hex']):
                category = 'software_tools'
                
            # Training
            elif any(k in txt for k in ['training', 'tutorial', 'video']) or \
                 any(href_lower.endswith(ext) for ext in ['.mp4', '.mov', '.avi']):
                category = 'training'

            # Design Resources
            elif any(href_lower.endswith(ext) for ext in ['.step', '.stp', '.igs', '.iges', '.dxf', '.dwg']):
                category = 'design_resources'

            # Documentation
            elif href_lower.endswith('.pdf') or href_lower.endswith('.doc') or href_lower.endswith('.docx'):
                category = 'documentation'
            
            # Download
            fname = ScraperUtils.download_file(href, dirs[category], ScraperUtils.clean_filename(txt))
            if fname:
                local_resources[category].append(f"{category}/{fname}")
                ScraperUtils.update_metadata(dirs[category], {
                    "file": fname,
                    "url": href,
                    "title": txt,
                    "category": category
                })

        # Save Final Data
        pdp_data = {
            "name": prod_name,
            "url": self.url,
            "description": description,
            "images": local_images,
            "block_diagrams": local_block_diagrams,
            "documentation": local_resources['documentation'],
            "design_resources": local_resources['design_resources'],
            "software_tools": local_resources['software_tools'],
            "training": local_resources['training'],
            "other": local_resources['other']
        }
        ScraperUtils.save_json(pdp_data, dirs['tables'], f"{safe_name}.json")

        # Markdown with custom utility
        md_content = ScraperUtils.write_overview_markdown(
            soup, 
            '.product-excerpt, .product-description, #productDescription', 
            section_title=None, 
            url=self.url
        )
        
        full_md = f"# {prod_name}\n\n{md_content}\n\n## Images\n"
        for img in local_images:
            full_md += f"![Image](../{img})\n"
            
        if local_block_diagrams:
            full_md += "\n## Block Diagrams\n"
            for bd in local_block_diagrams:
                 full_md += f"![Diagram](../{bd})\n"
             
        ScraperUtils.save_markdown(full_md, dirs['markdowns'], f"{safe_name}.md")
        logging.info(f"Finished processing {prod_name}")

if __name__ == "__main__":
    ScraperUtils.setup_logging()
    parser = argparse.ArgumentParser(description="KiloInternational Dynamic Scraper")
    parser.add_argument('url', help="Target URL to scrape")
    parser.add_argument('--out', help="Output directory path (optional)")
    args = parser.parse_args()

    engine = KiloEngine(args.url, args.out)
    engine.run()
