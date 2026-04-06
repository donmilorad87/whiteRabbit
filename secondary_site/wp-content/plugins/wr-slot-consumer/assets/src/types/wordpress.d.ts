/**
 * WordPress global type declarations for the consumer plugin.
 */

/* eslint-disable @typescript-eslint/no-explicit-any */

declare namespace wp {
	namespace blocks {
		function registerBlockType( name: string, settings: Record<string, any> ): void;
	}
	namespace element {
		function createElement( type: any, props?: any, ...children: any[] ): any;
		const Fragment: any;
	}
	namespace blockEditor {
		const InspectorControls: any;
		function useBlockProps(): Record<string, any>;
	}
	namespace components {
		const PanelBody: any;
		const RangeControl: any;
		const SelectControl: any;
		const TextControl: any;
		const ToggleControl: any;
		const ColorPicker: any;
		const Disabled: any;
		const __experimentalBoxControl: any;
	}
	namespace i18n {
		function __( text: string, domain?: string ): string;
	}
	const serverSideRender: any;
}

declare interface SlotData {
	id: number;
	title: string;
	slug: string;
	description: string;
	star_rating: number;
	featured_image: string;
	provider_name: string;
	rtp: number;
	min_wager: number;
	max_wager: number;
	status: string;
	created_at: string;
	updated_at: string;
}

declare interface WrScAdmin {
	ajaxUrl: string;
	syncNonce: string;
	settingsNonce: string;
}

declare interface Window {
	wrScAdmin: WrScAdmin;
}

declare const wrScAdmin: WrScAdmin;

declare function Toastify( options: {
	text: string;
	style?: Record<string, string>;
	duration?: number;
	gravity?: string;
	position?: string;
	offset?: { x: number; y: number };
} ): { showToast(): void };
