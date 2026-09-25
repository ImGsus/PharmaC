import zipfile
import xml.etree.ElementTree as ET

docx_path = r'c:\Users\Gman\OneDrive\Documents\PharmaC - Project\Pharmacy-management-system-main\Drop logs - updates\Gil\Final-Grp21-IT225-Chapters1-3-August-26-2026 (1).docx'

# Open the docx file as a zip
with zipfile.ZipFile(docx_path, 'r') as zip_ref:
    # Read the document.xml
    with zip_ref.open('word/document.xml') as xml_file:
        tree = ET.parse(xml_file)
        root = tree.getroot()
        
        # Define namespace
        ns = {'w': 'http://schemas.openxmlformats.org/wordprocessingml/2006/main'}
        
        # Extract all text
        texts = []
        for t in root.findall('.//w:t', ns):
            if t.text:
                texts.append(t.text)
        
        # Save to file
        with open(r'c:\temp\extracted_document.txt', 'w', encoding='utf-8') as out_f:
            out_f.write(''.join(texts))
        
        print(f"Extracted {len(texts)} text nodes")
        print(f"Total characters: {sum(len(t) for t in texts)}")
        print("\nFirst 3000 characters:")
        print(''.join(texts)[:3000])
