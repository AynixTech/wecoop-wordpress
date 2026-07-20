import sys
import json
import re

def normalizza_cf(cf):
    cf = re.sub(r'[^A-Z0-9]', '', cf.upper())
    return cf if len(cf) == 16 else ''


def normalizza_spazi(testo):
    return re.sub(r'\s+', ' ', testo or '').strip()


def estrai_codice_fiscale(testo):
    etichettati = re.findall(
        r'codice\s*fiscale\s*[:\-]?\s*([a-z0-9\s]{16,24})',
        testo,
        flags=re.IGNORECASE,
    )
    for valore in etichettati:
        cf = normalizza_cf(valore)
        if cf:
            return cf

    for valore in re.findall(r'\b[A-Z0-9]{16}\b', testo.upper()):
        cf = normalizza_cf(valore)
        if cf:
            return cf
    return ''


def estrai_nome_e_cognome(testo):
    pattern = (
        r'cognome\s*[:\-]?\s*'
        r'([A-ZÀ-ÖØ-Ý\'’\- ]+?)\s+'
        r'nome\s*[:\-]?\s*'
        r'([A-ZÀ-ÖØ-Ý\'’\- ]+?)'
        r'(?=\s+(?:sesso|data\s+di\s+nascita|codice\s+fiscale|comune|provincia)|$)'
    )
    match = re.search(pattern, testo, flags=re.IGNORECASE)
    if not match:
        return '', ''
    return normalizza_spazi(match.group(1)).upper(), normalizza_spazi(match.group(2)).upper()


def estrai_nome_e_cognome_da_filename(nome_file):
    """Fallback per i PDF CU che espongono solo il codice fiscale come testo.

    Il gestore documentale WECOOP usa il formato:
    CF - ANNO - COGNOME NOME (CF-progressivo).pdf.
    Per evitare attribuzioni errate, il fallback viene applicato soltanto quando
    il nominativo ha due o quattro parole, quindi con una divisione non ambigua.
    """
    base = re.sub(r'\.pdf$', '', nome_file or '', flags=re.IGNORECASE)
    match = re.search(
        r'^[A-Z0-9]{16}\s*-\s*\d{4}\s*-\s*(.+?)\s*\([A-Z0-9]{16}-\d+\)',
        base,
        flags=re.IGNORECASE,
    )
    if not match:
        return '', ''

    parole = normalizza_spazi(match.group(1)).split(' ')
    if len(parole) not in (2, 4):
        return '', ''

    separatore = len(parole) // 2
    return ' '.join(parole[:separatore]).upper(), ' '.join(parole[separatore:]).upper()

def main():
    if len(sys.argv) < 2:
        print(json.dumps({}), file=sys.stderr)
        sys.exit(1)
    pdf_path = sys.argv[1]
    original_filename = sys.argv[2] if len(sys.argv) > 2 else ''
    result = {"codice_fiscale": "", "nome": "", "cognome": "", "data_nascita": "", "luogo_nascita": "", "provincia_nascita": "", "sesso": ""}
    try:
        import pdfplumber

        with pdfplumber.open(pdf_path) as pdf:
            text = '\n'.join(page.extract_text() or '' for page in pdf.pages)
        text = normalizza_spazi(text)
        # Il testo è destinato esclusivamente al fallback AI lato server e non
        # viene restituito al browser né scritto nei log PHP.
        result["__extracted_text"] = text[:16000]

        # I layout delle CU cambiano fra anni e software di generazione: i campi
        # sono quindi cercati per etichetta, tollerando spazi, maiuscole e ritorni a capo.
        result["codice_fiscale"] = estrai_codice_fiscale(text)
        result["cognome"], result["nome"] = estrai_nome_e_cognome(text)
        if not result["cognome"] or not result["nome"]:
            result["__filename_cognome"], result["__filename_nome"] = estrai_nome_e_cognome_da_filename(original_filename)
        nascita = re.search(r'Data\s+di\s+nascita\s*([0-9]{2}[/-][0-9]{2}[/-][0-9]{4})\s*Comune\s*\/\s*Stato\s+Estero\s+di\s+nascita\s*([A-ZÀ-ÖØ-Ý \'’\-]+?)\s*Provincia\s*\(?([A-Z]{2})\)?', text, flags=re.IGNORECASE)
        if nascita:
            result["data_nascita"] = nascita.group(1)
            result["luogo_nascita"] = nascita.group(2).strip()
            result["provincia_nascita"] = nascita.group(3)
        sesso = re.search(r'Sesso\s*[:\-]?\s*(M|F)\b', text, flags=re.IGNORECASE)
        if sesso:
            result["sesso"] = sesso.group(1)
    except Exception as exc:
        result["__error"] = "Impossibile leggere il PDF: {}".format(exc)
    print(json.dumps(result))

if __name__ == '__main__':
    main()
