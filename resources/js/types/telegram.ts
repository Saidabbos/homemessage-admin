/**
 * Telegram Mini App SDK Types
 * @see https://core.telegram.org/bots/webapps
 */

export interface TelegramUser {
  id: number;
  is_bot?: boolean;
  first_name: string;
  last_name?: string;
  username?: string;
  language_code?: string;
  is_premium?: boolean;
  photo_url?: string;
}

export interface TelegramChat {
  id: number;
  type: 'group' | 'supergroup' | 'channel';
  title: string;
  username?: string;
  photo_url?: string;
}

export interface WebAppInitData {
  query_id?: string;
  user?: TelegramUser;
  receiver?: TelegramUser;
  chat?: TelegramChat;
  chat_type?: 'sender' | 'private' | 'group' | 'supergroup' | 'channel';
  chat_instance?: string;
  start_param?: string;
  can_send_after?: number;
  auth_date: number;
  hash: string;
}

export interface ThemeParams {
  bg_color?: string;
  text_color?: string;
  hint_color?: string;
  link_color?: string;
  button_color?: string;
  button_text_color?: string;
  secondary_bg_color?: string;
  header_bg_color?: string;
  accent_text_color?: string;
  section_bg_color?: string;
  section_header_text_color?: string;
  subtitle_text_color?: string;
  destructive_text_color?: string;
}

export interface MainButton {
  text: string;
  color: string;
  textColor: string;
  isVisible: boolean;
  isActive: boolean;
  isProgressVisible: boolean;
  setText: (text: string) => MainButton;
  onClick: (callback: () => void) => MainButton;
  offClick: (callback: () => void) => MainButton;
  show: () => MainButton;
  hide: () => MainButton;
  enable: () => MainButton;
  disable: () => MainButton;
  showProgress: (leaveActive?: boolean) => MainButton;
  hideProgress: () => MainButton;
  setParams: (params: {
    text?: string;
    color?: string;
    text_color?: string;
    is_active?: boolean;
    is_visible?: boolean;
  }) => MainButton;
}

export interface BackButton {
  isVisible: boolean;
  onClick: (callback: () => void) => BackButton;
  offClick: (callback: () => void) => BackButton;
  show: () => BackButton;
  hide: () => BackButton;
}

export interface HapticFeedback {
  impactOccurred: (style: 'light' | 'medium' | 'heavy' | 'rigid' | 'soft') => HapticFeedback;
  notificationOccurred: (type: 'error' | 'success' | 'warning') => HapticFeedback;
  selectionChanged: () => HapticFeedback;
}

export interface CloudStorage {
  setItem: (key: string, value: string, callback?: (error: Error | null, success?: boolean) => void) => void;
  getItem: (key: string, callback: (error: Error | null, value?: string) => void) => void;
  getItems: (keys: string[], callback: (error: Error | null, values?: Record<string, string>) => void) => void;
  removeItem: (key: string, callback?: (error: Error | null, success?: boolean) => void) => void;
  removeItems: (keys: string[], callback?: (error: Error | null, success?: boolean) => void) => void;
  getKeys: (callback: (error: Error | null, keys?: string[]) => void) => void;
}

export interface PopupParams {
  title?: string;
  message: string;
  buttons?: PopupButton[];
}

export interface PopupButton {
  id?: string;
  type?: 'default' | 'ok' | 'close' | 'cancel' | 'destructive';
  text?: string;
}

export interface WebApp {
  // Properties
  initData: string;
  initDataUnsafe: WebAppInitData;
  version: string;
  platform: string;
  colorScheme: 'light' | 'dark';
  themeParams: ThemeParams;
  isExpanded: boolean;
  viewportHeight: number;
  viewportStableHeight: number;
  headerColor: string;
  backgroundColor: string;
  isClosingConfirmationEnabled: boolean;

  // Components
  MainButton: MainButton;
  BackButton: BackButton;
  HapticFeedback: HapticFeedback;
  CloudStorage: CloudStorage;

  // Methods
  isVersionAtLeast: (version: string) => boolean;
  setHeaderColor: (color: 'bg_color' | 'secondary_bg_color' | string) => void;
  setBackgroundColor: (color: 'bg_color' | 'secondary_bg_color' | string) => void;
  enableClosingConfirmation: () => void;
  disableClosingConfirmation: () => void;
  onEvent: (eventType: string, callback: () => void) => void;
  offEvent: (eventType: string, callback: () => void) => void;
  sendData: (data: string) => void;
  switchInlineQuery: (query: string, choose_chat_types?: string[]) => void;
  openLink: (url: string, options?: { try_instant_view?: boolean }) => void;
  openTelegramLink: (url: string) => void;
  openInvoice: (url: string, callback?: (status: 'paid' | 'cancelled' | 'failed' | 'pending') => void) => void;
  showPopup: (params: PopupParams, callback?: (button_id?: string) => void) => void;
  showAlert: (message: string, callback?: () => void) => void;
  showConfirm: (message: string, callback?: (confirmed: boolean) => void) => void;
  showScanQrPopup: (params: { text?: string }, callback?: (data: string) => boolean) => void;
  closeScanQrPopup: () => void;
  readTextFromClipboard: (callback?: (data: string | null) => void) => void;
  requestWriteAccess: (callback?: (granted: boolean) => void) => void;
  requestContact: (callback?: (shared: boolean) => void) => void;
  ready: () => void;
  expand: () => void;
  close: () => void;
}

declare global {
  interface Window {
    Telegram?: {
      WebApp: WebApp;
    };
  }
}
