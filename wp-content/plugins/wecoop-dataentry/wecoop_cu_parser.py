import sys
import json
import pdfplumber
import re

def normalizza_cf(cf):
    cf = re.sub(r'[^A-Z0-9]', '', cf.upper())
    return cf if len(cf) == 16 else ''

def main():
    if len(sys.argv) < 2:
        print(json.dumps({}), file=sys.stderr)
        sys.exit(1)
    pdf_path = sys.argv[1]
    result = {"codice_fiscale": "", "nome": "", "cognome": "", "data_nascita": "", "luogo_nascita": "", "provincia_nascita": "", "sesso": ""}
    with pdfplumber.open(pdf_path) as pdf:
        text = ''
        for page in pdf.pages:
            text += page.extract_text()+'\n'
    # Cerca CF, nome, cognome, ecc.
    cf_match = re.search(r'Codice Fiscale\s*([A-Z0-9]{16})', text)
    if cf_match:
        result["codice_fiscale"] = normalizza_cf(cf_match.group(1))
    nomatch = re.search(r'Cognome\s*([A-Z\' \-]+)\s*Nome\s*([A-Z\' \-]+)', text)
    if nomatch:
        result["cognome"] = nomatch.group(1).strip()
        result["nome"]    = nomatch.group(2).strip()
    nascita = re.search(r'Data di nascita\s*([0-9]{2}/[0-9]{2}/[0-9]{4})\s*Comune\/Stato Estero di nascita\s*([A-Z \'\-]+)\s*Provincia \(([A-Z]{2})\)', text)
    if nascita:
        result["data_nascita"] = nascita.group(1)
        result["luogo_nascita"] = nascita.group(2).strip()
        result["provincia_nascita"] = nascita.group(3)
    sesso = re.search(r'Sesso\s*(M|F)', text)
    if sesso:
        result["sesso"] = sesso.group(1)
    print(json.dumps(result))

if __name__ == '__main__':
    main()
