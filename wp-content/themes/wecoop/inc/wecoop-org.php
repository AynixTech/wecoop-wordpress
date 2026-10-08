<?php
/**
 * Anagrafica istituzionale WECOOP APS — unica fonte per sede, CF, RUNTS, contatti.
 * Allineata a Documento Unico V1.0 e Informativa Privacy GDPR V1.0 (Settembre 2026).
 *
 * @package WeCoop
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'wecoop_org' ) ) {
	/**
	 * @return array<string, string>
	 */
	function wecoop_org() {
		static $data = null;
		if ( null !== $data ) {
			return $data;
		}
		$data = [
			'name'            => 'WECOOP APS',
			'codice_fiscale'  => '97977210158',
			'runts'           => 'n. 142852',
			'sede_legale'     => "Via Benefattori dell'Ospedale 3, 20159 Milano (MI)",
			'sede_operativa'  => 'Via Populonia 8, Milano',
			'phone'           => '+39 351 511 2113',
			'email'           => 'info@wecoop.org',
			'web'             => 'wecoop.org',
			'web_url'         => 'https://wecoop.org',
		];
		return $data;
	}
}

if ( ! function_exists( 'wecoop_org_titolare_html' ) ) {
	/**
	 * Blocco Titolare del trattamento (GDPR / Privacy) — come Informativa V1.0.
	 */
	function wecoop_org_titolare_html() {
		$o = wecoop_org();
		return '<strong>' . esc_html( $o['name'] ) . '</strong><br>'
			. 'Codice fiscale: ' . esc_html( $o['codice_fiscale'] ) . '<br>'
			. 'RUNTS: ' . esc_html( $o['runts'] ) . '<br>'
			. 'Sede: ' . esc_html( $o['sede_legale'] ) . '<br>'
			. 'E-mail: <a href="mailto:' . esc_attr( $o['email'] ) . '">' . esc_html( $o['email'] ) . '</a>';
	}
}

if ( ! function_exists( 'wecoop_org_anagrafica_html' ) ) {
	/**
	 * Blocco anagrafica istituzionale (Note legali): sede legale + operativa.
	 */
	function wecoop_org_anagrafica_html() {
		$o = wecoop_org();
		return '<strong>' . esc_html( $o['name'] ) . '</strong><br>'
			. 'Sede legale: ' . esc_html( $o['sede_legale'] ) . '<br>'
			. 'Sede operativa / Sportello: ' . esc_html( $o['sede_operativa'] ) . '<br>'
			. 'Codice fiscale: ' . esc_html( $o['codice_fiscale'] ) . '<br>'
			. 'RUNTS: ' . esc_html( $o['runts'] ) . '<br>'
			. 'Tel.: ' . esc_html( $o['phone'] ) . '<br>'
			. 'E-mail: <a href="mailto:' . esc_attr( $o['email'] ) . '">' . esc_html( $o['email'] ) . '</a><br>'
			. 'Web: <a href="' . esc_url( $o['web_url'] ) . '">' . esc_html( $o['web'] ) . '</a>';
	}
}
