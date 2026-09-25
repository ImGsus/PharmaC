from docx import Document

doc = Document(r'c:\Users\Gman\OneDrive\Documents\PharmaC - Project\Pharmacy-management-system-main\Drop logs - updates\Gil\Final-Grp21-IT225-Chapters1-3-August-26-2026 (1).docx')

full_text = []
for para in doc.paragraphs:
    if para.text.strip():
        full_text.append(para.text)

# Save to file
with open('c:\\temp\\docx_content.txt', 'w', encoding='utf-8') as f:
    f.write('\n'.join(full_text))

print(f'Extracted {len(full_text)} paragraphs')
print('Content saved to c:\\temp\\docx_content.txt')
print('First 50 paragraphs:')
print('\n'.join(full_text[:50]))
