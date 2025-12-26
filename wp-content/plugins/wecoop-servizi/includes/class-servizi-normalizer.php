<?php
/**
 * Servizi Normalizer
 * 
 * Normalizza i nomi dei servizi dalle chiavi standard app ai nomi italiani
 * e gestisce traduzioni multilingua per compatibilità con richieste vecchie
 * 
 * @package WECOOP_Servizi
 * @since 2.0.0
 */

if (!defined('ABSPATH')) exit;

class WECOOP_Servizi_Normalizer {
    
    /**
     * Mappa chiavi standard app -> nome italiano canonico
     * 
     * @var array
     */
    private static $servizi_standard = [
        // Servizi (livello 1)
        'caf_tax_assistance' => 'CAF - Assistenza Fiscale',
        'immigration_desk' => 'Sportello Immigrazione',
        'accounting_support' => 'Supporto Contabile',
        'tax_mediation' => 'Mediazione Fiscale',
    ];
    
    /**
     * Mappa categorie standard app -> nome italiano canonico
     * 
     * @var array
     */
    private static $categorie_standard = [
        // CAF
        'tax_return_730' => 'Dichiarazione dei Redditi (730)',
        'form_compilation' => 'Compilazione Modelli',
        
        // Sportello Immigrazione
        'residence_permit' => 'Permesso di Soggiorno',
        'citizenship' => 'Cittadinanza',
        'tourist_visa' => 'Visto Turistico',
        'asylum_request' => 'Richiesta Asilo',
        
        // Contabilità
        'income_tax_return' => 'Dichiarazione Redditi',
        'vat_number_opening' => 'Apertura Partita IVA',
        'accounting_management' => 'Gestione Contabilità',
        'tax_compliance' => 'Adempimenti Fiscali',
        'tax_consultation' => 'Consulenza Fiscale',
        
        // Mediazione
        'tax_debt_management' => 'Gestione Debiti Fiscali',
    ];
    
    /**
     * Mappa traduzioni multilingua -> nome canonico (per retrocompatibilità)
     * 
     * @var array
     */
    private static $traduzioni_legacy = [
        // Permesso di Soggiorno
        'Permesso di Soggiorno' => 'Permesso di Soggiorno',
        'Permiso de Residencia' => 'Permesso di Soggiorno',
        'Residence Permit' => 'Permesso di Soggiorno',
        'Titre de Séjour' => 'Permesso di Soggiorno',
        
        // Ricongiungimento Familiare
        'Ricongiungimento Familiare' => 'Ricongiungimento Familiare',
        'Reagrupación Familiar' => 'Ricongiungimento Familiare',
        'Family Reunification' => 'Ricongiungimento Familiare',
        'Regroupement Familial' => 'Ricongiungimento Familiare',
        
        // Cittadinanza
        'Cittadinanza Italiana' => 'Cittadinanza',
        'Cittadinanza' => 'Cittadinanza',
        'Ciudadanía Italiana' => 'Cittadinanza',
        'Ciudadanía' => 'Cittadinanza',
        'Italian Citizenship' => 'Cittadinanza',
        'Citizenship' => 'Cittadinanza',
        'Citoyenneté Italienne' => 'Cittadinanza',
        'Citoyenneté' => 'Cittadinanza',
        
        // CAF
        'CAF - Assistenza Fiscale' => 'CAF - Assistenza Fiscale',
        'CAF - Tax Assistance' => 'CAF - Assistenza Fiscale',
        'CAF - Asistencia Fiscal' => 'CAF - Assistenza Fiscale',
        
        // Dichiarazione Redditi
        'Dichiarazione dei Redditi (730)' => 'Dichiarazione dei Redditi (730)',
        'Dichiarazione Redditi' => 'Dichiarazione Redditi',
        'Tax Return' => 'Dichiarazione Redditi',
        'Declaración de Impuestos' => 'Dichiarazione Redditi',
        
        // Visto Turistico
        'Visto Turistico' => 'Visto Turistico',
        'Tourist Visa' => 'Visto Turistico',
        'Visa Turística' => 'Visto Turistico',
        
        // Altri servizi comuni
        'Sportello Immigrazione' => 'Sportello Immigrazione',
        'Immigration Desk' => 'Sportello Immigrazione',
        'Oficina de Inmigración' => 'Sportello Immigrazione',
        
        'Supporto Contabile' => 'Supporto Contabile',
        'Accounting Support' => 'Supporto Contabile',
        'Soporte Contable' => 'Supporto Contabile',
        
        'Mediazione Fiscale' => 'Mediazione Fiscale',
        'Tax Mediation' => 'Mediazione Fiscale',
        'Mediación Fiscal' => 'Mediazione Fiscale',
        
        // Richiesta Asilo
        'Richiesta Asilo' => 'Richiesta Asilo',
        'Asylum Request' => 'Richiesta Asilo',
        'Solicitud de Asilo' => 'Richiesta Asilo',
        
        // Compilazione Modelli
        'Compilazione Modelli' => 'Compilazione Modelli',
        'Form Compilation' => 'Compilazione Modelli',
        'Compilación de Formularios' => 'Compilazione Modelli',
        
        // Apertura Partita IVA
        'Apertura Partita IVA' => 'Apertura Partita IVA',
        'VAT Number Opening' => 'Apertura Partita IVA',
        'Apertura de Número IVA' => 'Apertura Partita IVA',
        
        // Gestione Contabilità
        'Gestione Contabilità' => 'Gestione Contabilità',
        'Accounting Management' => 'Gestione Contabilità',
        'Gestión de Contabilidad' => 'Gestione Contabilità',
        
        // Adempimenti Fiscali
        'Adempimenti Fiscali' => 'Adempimenti Fiscali',
        'Tax Compliance' => 'Adempimenti Fiscali',
        'Cumplimiento Fiscal' => 'Adempimenti Fiscali',
        
        // Consulenza Fiscale
        'Consulenza Fiscale' => 'Consulenza Fiscale',
        'Tax Consultation' => 'Consulenza Fiscale',
        'Consultoría Fiscal' => 'Consulenza Fiscale',
        
        // Gestione Debiti Fiscali
        'Gestione Debiti Fiscali' => 'Gestione Debiti Fiscali',
        'Tax Debt Management' => 'Gestione Debiti Fiscali',
        'Gestión de Deudas Fiscales' => 'Gestione Debiti Fiscali',
    ];
    
    /**
     * Normalizza nome servizio o categoria
     * 
     * @param string $valore Chiave standard, traduzione o nome italiano
     * @param string $tipo 'servizio' o 'categoria' (default: auto-detect)
     * @return string Nome italiano canonico
     */
    public static function normalize($valore, $tipo = null) {
        if (empty($valore)) {
            return $valore;
        }
        
        // Carica mappature custom
        self::load_custom_mappings();
        
        // 1. Cerca nelle chiavi standard servizi
        if (isset(self::$servizi_standard[$valore])) {
            return self::$servizi_standard[$valore];
        }
        
        // 2. Cerca nelle chiavi standard categorie
        if (isset(self::$categorie_standard[$valore])) {
            return self::$categorie_standard[$valore];
        }
        
        // 3. Cerca nelle traduzioni legacy (match esatto)
        if (isset(self::$traduzioni_legacy[$valore])) {
            return self::$traduzioni_legacy[$valore];
        }
        
        // 4. Cerca match case-insensitive
        foreach (self::$traduzioni_legacy as $key => $canonical) {
            if (strcasecmp($key, $valore) === 0) {
                return $canonical;
            }
        }
        
        // 5. Cerca match parziale (contiene)
        foreach (self::$traduzioni_legacy as $key => $canonical) {
            if (stripos($valore, $key) !== false || stripos($key, $valore) !== false) {
                return $canonical;
            }
        }
        
        // 6. Se non trovato, restituisci originale
        return $valore;
    }
    
    /**
     * Traduce chiave standard in nome italiano
     * 
     * @param string $key Chiave standard (es. 'residence_permit')
     * @param string $tipo 'servizio' o 'categoria'
     * @return string Nome italiano o chiave originale se non trovata
     */
    public static function translate_key($key, $tipo = 'categoria') {
        if ($tipo === 'servizio' && isset(self::$servizi_standard[$key])) {
            return self::$servizi_standard[$key];
        }
        
        if ($tipo === 'categoria' && isset(self::$categorie_standard[$key])) {
            return self::$categorie_standard[$key];
        }
        
        return $key;
    }
    
    /**
     * Ottieni chiave standard da nome italiano
     * 
     * @param string $nome Nome italiano
     * @param string $tipo 'servizio' o 'categoria'
     * @return string|null Chiave standard o null se non trovata
     */
    public static function get_standard_key($nome, $tipo = 'categoria') {
        $map = $tipo === 'servizio' ? self::$servizi_standard : self::$categorie_standard;
        
        $key = array_search($nome, $map);
        return $key !== false ? $key : null;
    }
    
    /**
     * Normalizza array di servizi mantenendo i conteggi
     * 
     * @param array $servizi_array Array [servizio => count]
     * @return array Array normalizzato
     */
    public static function normalize_array($servizi_array) {
        $normalized = [];
        
        foreach ($servizi_array as $servizio => $count) {
            $normalized_name = self::normalize($servizio);
            
            if (!isset($normalized[$normalized_name])) {
                $normalized[$normalized_name] = 0;
            }
            
            $normalized[$normalized_name] += $count;
        }
        
        return $normalized;
    }
    
    /**
     * Ottieni tutte le varianti di un servizio canonico
     * 
     * @param string $servizio_canonico Nome canonico del servizio
     * @return array Array di varianti
     */
    public static function get_variants($servizio_canonico) {
        $variants = [];
        
        // Cerca nelle traduzioni legacy
        foreach (self::$traduzioni_legacy as $variant => $canonical) {
            if ($canonical === $servizio_canonico) {
                $variants[] = $variant;
            }
        }
        
        // Cerca nelle chiavi standard
        foreach (self::$servizi_standard as $key => $nome) {
            if ($nome === $servizio_canonico) {
                $variants[] = $key;
            }
        }
        
        foreach (self::$categorie_standard as $key => $nome) {
            if ($nome === $servizio_canonico) {
                $variants[] = $key;
            }
        }
        
        return array_unique($variants);
    }
    
    /**
     * Ottieni lista servizi canonici unici
     * 
     * @return array Array di nomi canonici
     */
    public static function get_canonical_services() {
        $servizi = array_values(self::$servizi_standard);
        $categorie = array_values(self::$categorie_standard);
        $legacy = array_values(array_unique(self::$traduzioni_legacy));
        
        return array_values(array_unique(array_merge($servizi, $categorie, $legacy)));
    }
    
    /**
     * Ottieni tutte le chiavi standard servizi
     * 
     * @return array Array chiave => nome italiano
     */
    public static function get_servizi_standard() {
        return self::$servizi_standard;
    }
    
    /**
     * Ottieni tutte le chiavi standard categorie
     * 
     * @return array Array chiave => nome italiano
     */
    public static function get_categorie_standard() {
        return self::$categorie_standard;
    }
    
    /**
     * Aggiungi mappatura personalizzata
     * 
     * @param string $variante Nome variante
     * @param string $canonico Nome canonico
     */
    public static function add_mapping($variante, $canonico) {
        self::$traduzioni_legacy[$variante] = $canonico;
    }
    
    /**
     * Ottieni mappa completa (legacy + custom)
     * 
     * @return array Mappa completa
     */
    public static function get_map() {
        self::load_custom_mappings();
        
        return array_merge(
            self::$servizi_standard,
            self::$categorie_standard,
            self::$traduzioni_legacy
        );
    }
    
    /**
     * Carica mappature custom da database
     */
    private static function load_custom_mappings() {
        static $loaded = false;
        
        if ($loaded) {
            return;
        }
        
        $custom_mappings = get_option('wecoop_servizi_custom_mappings', []);
        if (!empty($custom_mappings)) {
            self::$traduzioni_legacy = array_merge(self::$traduzioni_legacy, $custom_mappings);
        }
        
        $loaded = true;
    }
}
