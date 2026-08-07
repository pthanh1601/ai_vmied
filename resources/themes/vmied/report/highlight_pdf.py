import sys
import json
import re
import fitz  # pip install PyMuPDF

def hex_to_rgb(hex_color):
    hex_color = hex_color.lstrip('#')
    return tuple(int(hex_color[i:i+2], 16)/255.0 for i in (0, 2, 4))

def main():
    if len(sys.argv) < 4:
        print("Usage: python3 highlight_pdf.py <input.pdf> <output.pdf> <data.json>")
        sys.exit(1)

    input_pdf = sys.argv[1]
    output_pdf = sys.argv[2]
    data_json = sys.argv[3]

    with open(data_json, 'r', encoding='utf-8') as f:
        highlights = json.load(f)

    doc = fitz.open(input_pdf)

    for item in highlights:
        text = item.get('text', '').strip()
        color = item.get('color', '#ffff00')
        if not text:
            continue
        
        rgb = hex_to_rgb(color)
        
        # Tách thành các từ thay vì câu để tránh lỗi ngắt dòng (line-break) ẩn trong PDF
        words = text.split()
        if not words:
            continue
        
        # Ghép thành các cụm 3 từ (chunk) để tìm kiếm chính xác hơn
        chunk_size = 3
        chunks = [' '.join(words[i:i+chunk_size]) for i in range(0, len(words), chunk_size)]
        
        for page in doc:
            for chunk in chunks:
                if len(chunk) < 2: continue
                text_instances = page.search_for(chunk)
                
                if not text_instances:
                    # Nếu cụm từ bị ngắt dòng, tìm từng từ một
                    for w in chunk.split():
                        if len(w) > 3: # Chỉ highlight từ dài để tránh dính từ vựng linh tinh
                            for inst in page.search_for(w):
                                annot = page.add_highlight_annot(inst)
                                annot.set_colors(stroke=rgb)
                                annot.update()
                else:
                    for inst in text_instances:
                        annot = page.add_highlight_annot(inst)
                        annot.set_colors(stroke=rgb)
                        annot.update()

    doc.save(output_pdf)
    doc.close()

if __name__ == '__main__':
    main()
