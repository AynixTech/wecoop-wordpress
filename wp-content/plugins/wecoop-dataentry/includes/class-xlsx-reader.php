<?php
/**
 * Lettore XLSX minimale in PHP puro (basato su ZipArchive + SimpleXML).
 * Non richiede librerie esterne. Legge un singolo foglio per nome.
 */

if (!defined('ABSPATH')) {
    exit;
}

class WeCoop_Xlsx_Reader {
    /**
     * Restituisce le righe del foglio indicato come array di array (indicizzati da 0 = colonna A).
     *
     * @param string $file_path Percorso del file .xlsx
     * @param string $sheet_name Nome del foglio (case-insensitive, trim)
     * @return array{rows: array, error: string}
     */
    public static function read_sheet($file_path, $sheet_name) {
        if (!class_exists('ZipArchive')) {
            return ['rows' => [], 'error' => 'Estensione ZipArchive non disponibile sul server.'];
        }

        if (!is_readable($file_path)) {
            return ['rows' => [], 'error' => 'File non leggibile.'];
        }

        $zip = new ZipArchive();
        if ($zip->open($file_path) !== true) {
            return ['rows' => [], 'error' => 'Impossibile aprire il file XLSX (ZIP non valido).'];
        }

        try {
            $shared = self::read_shared_strings($zip);
            $sheet_path = self::find_sheet_path($zip, $sheet_name);

            if ($sheet_path === '') {
                return ['rows' => [], 'error' => sprintf('Foglio "%s" non trovato nel file.', $sheet_name)];
            }

            $xml_raw = $zip->getFromName($sheet_path);
            if ($xml_raw === false) {
                return ['rows' => [], 'error' => 'Impossibile leggere il contenuto del foglio.'];
            }

            $rows = self::parse_sheet_xml($xml_raw, $shared);

            return ['rows' => $rows, 'error' => ''];
        } finally {
            $zip->close();
        }
    }

    private static function load_xml($content) {
        if ($content === false || $content === '') {
            return null;
        }

        $previous = libxml_use_internal_errors(true);
        $xml = simplexml_load_string($content);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        return $xml ?: null;
    }

    private static function read_shared_strings(ZipArchive $zip) {
        $content = $zip->getFromName('xl/sharedStrings.xml');
        $strings = [];

        $xml = self::load_xml($content);
        if (!$xml) {
            return $strings;
        }

        foreach ($xml->si as $si) {
            $strings[] = self::extract_si_text($si);
        }

        return $strings;
    }

    private static function extract_si_text(SimpleXMLElement $si) {
        // Stringa semplice <t>...</t>
        if (isset($si->t)) {
            return (string) $si->t;
        }

        // Rich text: concatena tutti i <r><t>
        $text = '';
        foreach ($si->r as $r) {
            if (isset($r->t)) {
                $text .= (string) $r->t;
            }
        }

        return $text;
    }

    private static function find_sheet_path(ZipArchive $zip, $sheet_name) {
        $target = self::normalize($sheet_name);

        $workbook = self::load_xml($zip->getFromName('xl/workbook.xml'));
        if (!$workbook) {
            return '';
        }

        // Mappa relationship id -> target (xl/worksheets/sheetN.xml)
        $rels = self::load_xml($zip->getFromName('xl/_rels/workbook.xml.rels'));
        $rid_to_target = [];
        if ($rels) {
            foreach ($rels->Relationship as $rel) {
                $id = (string) $rel['Id'];
                $tgt = (string) $rel['Target'];
                if ($id !== '' && $tgt !== '') {
                    $rid_to_target[$id] = $tgt;
                }
            }
        }

        $sheets = $workbook->sheets ? $workbook->sheets->sheet : [];
        foreach ($sheets as $sheet) {
            $name = self::normalize((string) $sheet['name']);
            if ($name !== $target) {
                continue;
            }

            // Recupera r:id (namespace relationships)
            $rid = '';
            foreach ($sheet->attributes('http://schemas.openxmlformats.org/officeDocument/2006/relationships') as $attr_name => $attr_value) {
                if ($attr_name === 'id') {
                    $rid = (string) $attr_value;
                    break;
                }
            }

            if ($rid !== '' && isset($rid_to_target[$rid])) {
                $tgt = ltrim($rid_to_target[$rid], '/');
                if (strpos($tgt, 'xl/') !== 0) {
                    $tgt = 'xl/' . $tgt;
                }
                return $tgt;
            }
        }

        return '';
    }

    private static function parse_sheet_xml($xml_raw, array $shared) {
        $xml = self::load_xml($xml_raw);
        if (!$xml || !isset($xml->sheetData)) {
            return [];
        }

        $rows = [];
        foreach ($xml->sheetData->row as $row) {
            $row_index = (int) $row['r']; // 1-based
            $cells = [];
            $max_col = 0;

            foreach ($row->c as $c) {
                $ref = (string) $c['r']; // es. "M9"
                $col_index = self::column_to_index($ref); // 0-based
                $type = (string) $c['t'];
                $value = self::cell_value($c, $type, $shared);
                $cells[$col_index] = $value;
                if ($col_index > $max_col) {
                    $max_col = $col_index;
                }
            }

            // Normalizza in array indicizzato 0..max_col
            $normalized = [];
            for ($i = 0; $i <= $max_col; $i++) {
                $normalized[$i] = $cells[$i] ?? '';
            }

            $rows[$row_index] = $normalized;
        }

        // Riempi eventuali righe mancanti per avere indici contigui
        if (empty($rows)) {
            return [];
        }

        ksort($rows);
        $result = [];
        $last = max(array_keys($rows));
        for ($r = 1; $r <= $last; $r++) {
            $result[$r] = $rows[$r] ?? [];
        }

        return $result;
    }

    private static function cell_value(SimpleXMLElement $c, $type, array $shared) {
        if ($type === 's') {
            $idx = (int) $c->v;
            return $shared[$idx] ?? '';
        }

        if ($type === 'inlineStr') {
            if (isset($c->is)) {
                return self::extract_si_text($c->is);
            }
            return '';
        }

        if ($type === 'str') {
            return (string) $c->v;
        }

        if (!isset($c->v)) {
            return '';
        }

        return (string) $c->v;
    }

    /**
     * Converte un riferimento di cella (es. "AB12") nell'indice colonna 0-based.
     */
    private static function column_to_index($ref) {
        if (!preg_match('/^([A-Z]+)/', $ref, $m)) {
            return 0;
        }

        $letters = $m[1];
        $index = 0;
        $len = strlen($letters);
        for ($i = 0; $i < $len; $i++) {
            $index = $index * 26 + (ord($letters[$i]) - ord('A') + 1);
        }

        return $index - 1;
    }

    /**
     * Converte un valore seriale data Excel in stringa Y-m-d.
     * Restituisto stringa vuota se non convertibile.
     */
    public static function excel_serial_to_date($value) {
        $value = trim((string) $value);

        if ($value === '' || !is_numeric($value)) {
            return '';
        }

        $serial = (float) $value;
        // Excel: 1 = 1900-01-01, con il bug del 1900 bisestile.
        if ($serial < 1 || $serial > 80000) {
            return '';
        }

        // Base 1899-12-30 per compensare il bug Excel del 29/02/1900.
        $base = strtotime('1899-12-30 00:00:00 UTC');
        $timestamp = $base + (int) round($serial * 86400);

        return gmdate('Y-m-d', $timestamp);
    }

    public static function normalize($value) {
        return strtolower(trim((string) $value));
    }
}
