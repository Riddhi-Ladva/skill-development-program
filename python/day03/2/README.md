# KiloInternational Scraper

A dynamic web scraper for extracting categories, product listings, and product details (PDPs) from KiloInternational.

## Setup

1. Install Python (3.x).
2. Install dependencies:
   ```bash
   pip install -r requirements.txt
   ```

## Usage

You can run the scraper in three ways to get specific data in specific folders.

### 1. Scrape a Category (Nav/Listing Structure)
Extracts the category structure to `output/category`.
```bash
python crawl.py --url https://www.kilointernational.com/knobs --out output/category
```

### 2. Scrape a Product Listing (Part)
Extracts products from a category page to `output/part`.
```bash
python crawl.py --url https://www.kilointernational.com/knobs/0c1a9syupscnvm7r9gikpps0bpzjdh --out output/part
```

### 3. Scrape a Product Detail Page (Part 1)
Extracts full details (specs, images, docs) for a product to `output/part1`.
```bash
python crawl.py --url https://www.kilointernational.com/dials/400-series --out output/part1
```

### Automatic Mode
You can also just run the script without arguments to crawl the entire site sequentially:
```bash
python crawl.py
```
