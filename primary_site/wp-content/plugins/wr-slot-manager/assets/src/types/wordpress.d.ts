/**
 * WordPress global type declarations for the manager plugin.
 */

/* eslint-disable @typescript-eslint/no-explicit-any */

declare namespace wp {
	namespace blocks {
		function registerBlockType( name: string, settings: Record<string, any> ): void;
	}
	namespace element {
		function createElement( type: any, props?: any, ...children: any[] ): any;
		function useEffect( effect: () => void | ( () => void ), deps?: any[] ): void;
		function useRef<T>( initial: T ): { current: T };
		function useState<T>( initial: T ): [ T, ( value: T | ( ( prev: T ) => T ) ) => void ];
		function useCallback<T extends ( ...args: any[] ) => any>( callback: T, deps: any[] ): T;
	}
	namespace blockEditor {
		const MediaUpload: any;
		const MediaUploadCheck: any;
	}
	namespace components {
		const TextControl: any;
		const TextareaControl: any;
		const Button: any;
		const Placeholder: any;
		const Tooltip: any;
	}
	namespace data {
		function useSelect<T>( selector: ( select: any ) => T, deps?: any[] ): T;
		function useDispatch( store: string ): Record<string, ( ...args: any[] ) => any>;
	}
	namespace i18n {
		function __( text: string, domain?: string ): string;
	}
}

declare interface WrSmAdmin {
	ajaxUrl: string;
	apiNonce: string;
	sitesNonce: string;
	settingsNonce: string;
}

declare interface Window {
	wrSmAdmin: WrSmAdmin;
}

declare const wrSmAdmin: WrSmAdmin;

declare function Toastify( options: {
	text: string;
	style?: Record<string, string>;
	duration?: number;
	gravity?: string;
	position?: string;
	offset?: { x: number; y: number };
} ): { showToast(): void };
