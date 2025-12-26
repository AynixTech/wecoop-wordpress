<?php
/**
 * Servizi Normalizer
 * 
 * Normalizza i nomi dei servizi in lingue diverse per le statistiche
 * 
 * @package WECOOP_Servizi
 * @since 1.0.0
 */

if (!defined('ABSPATH')) exit;

class WECOOP_Servizi_Normalizer {
    
    /**
     * Mappa servizi multilingua -> nome canonico
     * 
     * @var array
     */
    private static $servizi_map = [
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
        'Cittadinanza Italiana' => 'Cittadinanza Italiana',
        'Ciudadanía Italiana' => 'Cittadinanza Italiana',
        'Italian Citizenship' => 'Cittadinanza Italiana',
        'Citoyenneté Italienne' => 'Cittadinanza Italiana',
        
        // Conversione Patente
        'Conversione Patente' => 'Conversione Patente',
        'Conversión de Licencia' => 'Conversione Patente',
        'License Conversion' => 'Conversione Patente',
        'Conversion de Permis' => 'Conversione Patente',
        
        // Nulla Osta
        'Nulla Osta al Lavoro' => 'Nulla Osta al Lavoro',
        'Autorización de Trabajo' => 'Nulla Osta al Lavoro',
        'Work Authorization' => 'Nulla Osta al Lavoro',
        'Autorisation de Travail' => 'Nulla Osta al Lavoro',
        
        // Iscrizione SSN
        'Iscrizione SSN' => 'Iscrizione SSN',
        'Inscripción SSN' => 'Iscrizione SSN',
        'SSN Registration' => 'Iscrizione SSN',
        'Inscription SSN' => 'Iscrizione SSN',
        
        // Codice Fiscale
        'Codice Fiscale' => 'Codice Fiscale',
        'Código Fiscal' => 'Codice Fiscale',
        'Tax Code' => 'Codice Fiscale',
        'Code Fiscal' => 'Codice Fiscale',
        
        // Carta d'Identità
        'Carta d\'Identità' => 'Carta d\'Identità',
        'Tarjeta de Identidad' => 'Carta d\'Identità',
        'Identity Card' => 'Carta d\'Identità',
        'Carte d\'Identité' => 'Carta d\'Identità',
        
        // Anagrafe
        'Iscrizione Anagrafe' => 'Iscrizione Anagrafe',
        'Inscripción Registro Civil' => 'Iscrizione Anagrafe',
        'Registry Office Registration' => 'Iscrizione Anagrafe',
        'Inscription à l\'État Civil' => 'Iscrizione Anagrafe',
        
        // Assegni Familiari
        'Assegni Familiari' => 'Assegni Familiari',
        'Asignaciones Familiares' => 'Assegni Familiari',
        'Family Allowances' => 'Assegni Familiari',
        'Allocations Familiales' => 'Assegni Familiari',
        
        // Assistenza Legale
        'Assistenza Legale' => 'Assistenza Legale',
        'Asistencia Legal' => 'Assistenza Legale',
        'Legal Assistance' => 'Assistenza Legale',
        'Assistance Juridique' => 'Assistenza Legale',
        
        // Traduzione Documenti
        'Traduzione Documenti' => 'Traduzione Documenti',
        'Traducción de Documentos' => 'Traduzione Documenti',
        'Document Translation' => 'Traduzione Documenti',
        'Traduction de Documents' => 'Traduzione Documenti',
        
        // Legalizzazione
        'Legalizzazione Documenti' => 'Legalizzazione Documenti',
        'Legalización de Documentos' => 'Legalizzazione Documenti',
        'Document Legalization' => 'Legalizzazione Documenti',
        'Légalisation de Documents' => 'Legalizzazione Documenti',
        
        // Consulenza Immigrazione
        'Consulenza Immigrazione' => 'Consulenza Immigrazione',
        'Consultoría de Inmigración' => 'Consulenza Immigrazione',
        'Immigration Consulting' => 'Consulenza Immigrazione',
        'Conseil en Immigration' => 'Consulenza Immigrazione',
        
        // Rinnovo Permesso
        'Rinnovo Permesso di Soggiorno' => 'Rinnovo Permesso di Soggiorno',
        'Renovación de Permiso' => 'Rinnovo Permesso di Soggiorno',
        'Permit Renewal' => 'Rinnovo Permesso di Soggiorno',
        'Renouvellement de Permis' => 'Rinnovo Permesso di Soggiorno',
    ];
    
    /**
     * Normalizza nome servizio
     * 
     * @param string $servizio Nome servizio originale
     * @return string Nome servizio normalizzato
     */
    public static function normalize($servizio) {
        if (empty($servizio)) {
            return $servizio;
        }
        
        // Carica mappature custom
        self::load_custom_mappings();
        
        // Cerca match esatto
        if (isset(self::$servizi_map[$servizio])) {
            return self::$servizi_map[$servizio];
        }
        
        // Cerca match case-insensitive
        foreach (self::$servizi_map as $key => $value) {
            if (strcasecmp($key, $servizio) === 0) {
                return $value;
            }
        }
        
        // Cerca match parziale (contiene)
        foreach (self::$servizi_map as $key => $value) {
            if (stripos($servizio, $key) !== false || stripos($key, $servizio) !== false) {
                return $value;
            }
        }
        
        // Se non trovato, restituisci originale
        return $servizio;
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
            self::$servizi_map = array_merge(self::$servizi_map, $custom_mappings);
        }
        
        $loaded = true;
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
     * Ottieni tutte le varianti di un servizio
     * 
     * @param string $servizio_canonico Nome canonico del servizio
     * @return array Array di varianti
     */
    public static function get_variants($servizio_canonico) {
        $variants = [];
        
        foreach (self::$servizi_map as $variant => $canonical) {
            if ($canonical === $servizio_canonico) {
                $variants[] = $variant;
            }
        }
        
        return $variants;
    }
    
    /**
     * Ottieni lista servizi canonici unici
     * 
     * @return array Array di nomi canonici
     */
    public static function get_canonical_services() {
        return array_values(array_unique(self::$servizi_map));
    }
    
    /**
     * Aggiungi mappatura personalizzata
     * 
     * @param string $variante Nome variante
     * @param string $canonico Nome canonico
     */
    public static function add_mapping($variante, $canonico) {
        self::$servizi_map[$variante] = $canonico;
    }
    
    /**
     * Ottieni mappa completa
     * 
     * @return array Mappa completa
     */
    public static function get_map() {
        return self::$servizi_map;
    }
}
