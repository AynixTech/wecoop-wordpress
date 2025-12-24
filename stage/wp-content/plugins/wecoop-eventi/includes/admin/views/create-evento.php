<?php
/**
 * View: Creazione Nuovo Evento
 */
if (!defined('ABSPATH')) exit;
?>

<div class="wrap wecoop-evento-wrap">
    <h1 class="wecoop-page-title">
        <span class="dashicons dashicons-calendar-alt"></span>
        Crea Nuovo Evento
    </h1>
    
    <form method="post" enctype="multipart/form-data" class="wecoop-evento-form">
        <?php wp_nonce_field('create_evento', 'evento_nonce'); ?>
        
        <!-- Informazioni Base -->
        <div class="wecoop-card">
            <div class="wecoop-card-header">
                <h2>📋 Informazioni Base</h2>
            </div>
            <div class="wecoop-card-body">
                <div class="wecoop-form-row">
                    <div class="wecoop-form-field full">
                        <label class="required">Titolo Evento</label>
                        <input type="text" name="titolo" required placeholder="Es: Assemblea Generale 2025" class="large-text">
                    </div>
                </div>
                
                <div class="wecoop-form-row">
                    <div class="wecoop-form-field full">
                        <label>Descrizione Breve</label>
                        <textarea name="excerpt" rows="3" class="large-text" placeholder="Una breve descrizione dell'evento che apparirà nell'anteprima"></textarea>
                    </div>
                </div>
                
                <div class="wecoop-form-row">
                    <div class="wecoop-form-field">
                        <label>Stato</label>
                        <select name="stato" class="wecoop-select">
                            <option value="attivo">✅ Attivo</option>
                            <option value="annullato">❌ Annullato</option>
                            <option value="completato">✔️ Completato</option>
                        </select>
                    </div>
                    
                    <div class="wecoop-form-field">
                        <label>Immagine di Copertina</label>
                        <input type="file" name="thumbnail" accept="image/*">
                        <p class="description">Formati: JPG, PNG. Dimensione consigliata: 1200x630px</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Localizzazione -->
        <div class="wecoop-card">
            <div class="wecoop-card-header">
                <h2>📍 Dove si Svolge</h2>
            </div>
            <div class="wecoop-card-body">
                <div class="wecoop-form-row">
                    <div class="wecoop-form-field">
                        <label class="checkbox-label">
                            <input type="checkbox" name="evento_online" value="1" id="evento-online-check">
                            <span>🌐 Evento Online</span>
                        </label>
                    </div>
                    
                    <div class="wecoop-form-field" id="link-online-field" style="display:none;">
                        <label>Link Online</label>
                        <input type="url" name="link_online" class="regular-text" placeholder="https://meet.google.com/xxx-xxxx-xxx">
                        <p class="description">Inserisci il link per la videoconferenza (Zoom, Google Meet, etc.)</p>
                    </div>
                </div>
                
                <div class="wecoop-form-row" id="luogo-fisico-fields">
                    <div class="wecoop-form-field full">
                        <label class="required">Nome del Luogo</label>
                        <input type="text" name="luogo" class="large-text" placeholder="Es: Sala Conferenze WeCoopHub">
                    </div>
                </div>
                
                <div class="wecoop-form-row" id="indirizzo-fields">
                    <div class="wecoop-form-field">
                        <label>Indirizzo</label>
                        <input type="text" name="indirizzo" class="regular-text" placeholder="Via/Piazza e numero civico">
                    </div>
                    
                    <div class="wecoop-form-field">
                        <label>Città</label>
                        <input type="text" name="citta" class="regular-text" placeholder="Es: Roma">
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Date e Orari -->
        <div class="wecoop-card">
            <div class="wecoop-card-header">
                <h2>📅 Quando si Svolge</h2>
            </div>
            <div class="wecoop-card-body">
                <div class="wecoop-form-row">
                    <div class="wecoop-form-field">
                        <label class="required">Data Inizio</label>
                        <input type="date" name="data_inizio" required>
                    </div>
                    
                    <div class="wecoop-form-field">
                        <label class="required">Ora Inizio</label>
                        <input type="time" name="ora_inizio" required>
                    </div>
                </div>
                
                <div class="wecoop-form-row">
                    <div class="wecoop-form-field">
                        <label>Data Fine</label>
                        <input type="date" name="data_fine">
                        <p class="description">Lascia vuoto se l'evento dura un solo giorno</p>
                    </div>
                    
                    <div class="wecoop-form-field">
                        <label>Ora Fine</label>
                        <input type="time" name="ora_fine">
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Iscrizioni -->
        <div class="wecoop-card">
            <div class="wecoop-card-header">
                <h2>👥 Gestione Iscrizioni</h2>
            </div>
            <div class="wecoop-card-body">
                <div class="wecoop-form-row">
                    <div class="wecoop-form-field">
                        <label class="checkbox-label">
                            <input type="checkbox" name="richiede_iscrizione" value="1" id="richiede-iscrizione-check">
                            <span>✋ Richiede Iscrizione</span>
                        </label>
                        <p class="description">Se attivo, gli utenti potranno iscriversi tramite l'app</p>
                    </div>
                    
                    <div class="wecoop-form-field" id="posti-field" style="display:none;">
                        <label>Posti Disponibili</label>
                        <input type="number" name="posti_disponibili" min="0" value="0">
                        <p class="description">0 = posti illimitati</p>
                    </div>
                </div>
                
                <div class="wecoop-form-row" id="prezzo-field" style="display:none;">
                    <div class="wecoop-form-field">
                        <label>Prezzo (€)</label>
                        <input type="number" name="prezzo" min="0" step="0.01" value="0.00">
                        <p class="description">Lascia 0 per evento gratuito</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Organizzatore -->
        <div class="wecoop-card">
            <div class="wecoop-card-header">
                <h2>👤 Informazioni Organizzatore</h2>
            </div>
            <div class="wecoop-card-body">
                <div class="wecoop-form-row">
                    <div class="wecoop-form-field">
                        <label>Nome Organizzatore</label>
                        <input type="text" name="organizzatore" value="WeCoop" class="regular-text">
                    </div>
                    
                    <div class="wecoop-form-field">
                        <label>Email Organizzatore</label>
                        <input type="email" name="email_organizzatore" class="regular-text" placeholder="info@stage.wecoop.org">
                    </div>
                </div>
                
                <div class="wecoop-form-row">
                    <div class="wecoop-form-field">
                        <label>Telefono Organizzatore</label>
                        <input type="tel" name="telefono_organizzatore" class="regular-text" placeholder="+39 xxx xxx xxxx">
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Programma -->
        <div class="wecoop-card">
            <div class="wecoop-card-header">
                <h2>📝 Programma e Descrizione</h2>
            </div>
            <div class="wecoop-card-body">
                <div class="wecoop-form-row">
                    <div class="wecoop-form-field full">
                        <label>Programma dell'Evento</label>
                        <textarea name="programma" rows="8" class="large-text" placeholder="Descrivi il programma dettagliato dell'evento...&#10;&#10;Es:&#10;10:00 - Registrazione partecipanti&#10;10:30 - Apertura e saluti&#10;11:00 - Interventi&#10;13:00 - Pausa pranzo&#10;..."></textarea>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Contenuto Multilingua -->
        <div class="wecoop-card">
            <div class="wecoop-card-header">
                <h2>🌍 Traduzioni</h2>
            </div>
            <div class="wecoop-card-body">
                <div class="wecoop-tabs">
                    <button type="button" class="wecoop-tab active" data-lang="it">🇮🇹 Italiano</button>
                    <button type="button" class="wecoop-tab" data-lang="en">🇬🇧 English</button>
                    <button type="button" class="wecoop-tab" data-lang="es">🇪🇸 Español</button>
                </div>
                
                <div class="wecoop-tab-content active" id="content-it">
                    <div class="wecoop-form-row">
                        <div class="wecoop-form-field full">
                            <label>Titolo (Italiano)</label>
                            <input type="text" name="titolo_it" class="large-text" placeholder="Titolo in italiano">
                        </div>
                    </div>
                    <div class="wecoop-form-row">
                        <div class="wecoop-form-field full">
                            <label>Descrizione (Italiano)</label>
                            <textarea name="descrizione_it" rows="5" class="large-text" placeholder="Descrizione completa in italiano"></textarea>
                        </div>
                    </div>
                </div>
                
                <div class="wecoop-tab-content" id="content-en">
                    <div class="wecoop-form-row">
                        <div class="wecoop-form-field full">
                            <label>Title (English)</label>
                            <input type="text" name="titolo_en" class="large-text" placeholder="Title in English">
                        </div>
                    </div>
                    <div class="wecoop-form-row">
                        <div class="wecoop-form-field full">
                            <label>Description (English)</label>
                            <textarea name="descrizione_en" rows="5" class="large-text" placeholder="Full description in English"></textarea>
                        </div>
                    </div>
                </div>
                
                <div class="wecoop-tab-content" id="content-es">
                    <div class="wecoop-form-row">
                        <div class="wecoop-form-field full">
                            <label>Título (Español)</label>
                            <input type="text" name="titolo_es" class="large-text" placeholder="Título en español">
                        </div>
                    </div>
                    <div class="wecoop-form-row">
                        <div class="wecoop-form-field full">
                            <label>Descripción (Español)</label>
                            <textarea name="descrizione_es" rows="5" class="large-text" placeholder="Descripción completa en español"></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Azioni -->
        <div class="wecoop-form-actions">
            <button type="submit" name="create_evento" class="button button-primary button-hero">
                <span class="dashicons dashicons-yes"></span>
                Crea Evento
            </button>
            <a href="<?php echo admin_url('edit.php?post_type=evento'); ?>" class="button button-large">
                Annulla
            </a>
        </div>
    </form>
</div>
