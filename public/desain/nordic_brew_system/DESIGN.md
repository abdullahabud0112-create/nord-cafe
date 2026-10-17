---
name: Nordic Brew System
colors:
  surface: '#fbf9f8'
  surface-dim: '#dbdad9'
  surface-bright: '#fbf9f8'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#f5f3f3'
  surface-container: '#efeded'
  surface-container-high: '#e9e8e7'
  surface-container-highest: '#e4e2e2'
  on-surface: '#1b1c1c'
  on-surface-variant: '#444748'
  inverse-surface: '#303031'
  inverse-on-surface: '#f2f0f0'
  outline: '#747878'
  outline-variant: '#c4c7c7'
  surface-tint: '#5f5e5e'
  primary: '#000000'
  on-primary: '#ffffff'
  primary-container: '#1c1b1b'
  on-primary-container: '#858383'
  inverse-primary: '#c8c6c5'
  secondary: '#99460d'
  on-secondary: '#ffffff'
  secondary-container: '#fd9356'
  on-secondary-container: '#6f2e00'
  tertiary: '#000000'
  on-tertiary: '#ffffff'
  tertiary-container: '#1c1b1a'
  on-tertiary-container: '#868382'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#e5e2e1'
  primary-fixed-dim: '#c8c6c5'
  on-primary-fixed: '#1c1b1b'
  on-primary-fixed-variant: '#474746'
  secondary-fixed: '#ffdbca'
  secondary-fixed-dim: '#ffb68f'
  on-secondary-fixed: '#331100'
  on-secondary-fixed-variant: '#773200'
  tertiary-fixed: '#e6e2df'
  tertiary-fixed-dim: '#cac6c4'
  on-tertiary-fixed: '#1c1b1a'
  on-tertiary-fixed-variant: '#484645'
  background: '#fbf9f8'
  on-background: '#1b1c1c'
  surface-variant: '#e4e2e2'
  background-warm: '#F5EBDD'
  status-processing: '#F59E0B'
  status-completed: '#10B981'
  status-unpaid: '#6B6B6B'
typography:
  headline-lg:
    fontFamily: Inter
    fontSize: 32px
    fontWeight: '700'
    lineHeight: '1.2'
    letterSpacing: -0.02em
  headline-md:
    fontFamily: Inter
    fontSize: 24px
    fontWeight: '700'
    lineHeight: '1.3'
  headline-sm:
    fontFamily: Inter
    fontSize: 20px
    fontWeight: '600'
    lineHeight: '1.4'
  body-lg:
    fontFamily: Inter
    fontSize: 16px
    fontWeight: '400'
    lineHeight: '1.6'
  body-md:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: '400'
    lineHeight: '1.6'
  body-secondary:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: '400'
    lineHeight: '1.6'
  label-caps:
    fontFamily: Inter
    fontSize: 12px
    fontWeight: '600'
    lineHeight: '1'
    letterSpacing: 0.05em
rounded:
  sm: 0.25rem
  DEFAULT: 0.5rem
  md: 0.75rem
  lg: 1rem
  xl: 1.5rem
  full: 9999px
spacing:
  margin-mobile: 20px
  gutter: 16px
  unit-xs: 4px
  unit-sm: 8px
  unit-md: 16px
  unit-lg: 24px
  unit-xl: 40px
---

## Brand & Style

The design system is a "Safe" and reliable utility-first framework tailored for high-traffic cafe environments. It balances the precision of Nordic minimalism with the tactile warmth of a boutique coffee house. The primary objective is to facilitate high-speed decision-making and order management through a professional, clean, and highly legible interface.

The visual style is **Corporate / Modern**, emphasizing a systematic approach to hierarchy and state management. By utilizing a neutral, high-contrast base, the system ensures that the product—the coffee and food—remains the focal point. The emotional response is one of reliability and understated premium quality, making it equally suitable for a customer-facing kiosk or a high-pressure cashier dashboard.

## Colors

This design system uses a palette rooted in natural, coffee-inspired tones.

- **Primary Canvas:** The background utilizes `#F5EBDD` (Warm Cream) to provide a soft, organic surface that reduces eye strain compared to pure white.
- **Brand Core:** `#1A1A1A` (Deep Espresso) is the primary color, used for critical UI elements, heavy headings, and "Paid" status indicators to convey authority and stability.
- **The Accent:** `#C1642B` (Burnt Orange) provides a warm, energetic secondary accent for highlights, primary call-to-actions, and interactive indicators.
- **Functional States:** Statuses are clearly demarcated using high-visibility semantic colors: Amber for processing, Green for completion, and a neutral Gray for unpaid states.
- **Typography Neutrals:** Primary text uses the Deep Espresso for maximum legibility, while secondary metadata uses `#6B6B6B` to create a clear informational hierarchy.

## Typography

The system relies exclusively on **Inter** to maintain a utilitarian and systematic aesthetic. This typeface was chosen for its exceptional legibility on small screens and its neutral, professional character.

- **Headlines:** Set in bold weights with tight letter-spacing to create a "blocky," structured feel that anchors the page. 
- **Body Text:** Designed for readability with generous line heights. The distinction between primary and secondary information is handled through color (`#1A1A1A` vs `#6B6B6B`) rather than font size alone.
- **Labels:** Small, uppercase labels are utilized for non-interactive metadata and categories to provide a clear visual departure from interactive body text.

## Layout & Spacing

The layout is optimized for a **Mobile Portrait (390x844px)** experience. It uses a fluid model that prioritizes vertical scrolling and thumb-friendly interaction zones.

- **Grid Model:** A standard mobile layout with 20px side margins. Elements typically span the full width of the safe area or are arranged in 2-column grids (e.g., menu items).
- **Rhythm:** Spacing follows an 8px base unit. 16px (unit-md) is the standard gap between related elements, while 40px (unit-xl) is used to separate major logical sections.
- **Reflow:** On wider devices, content remains centered in a max-width container to preserve the vertical scanning pattern essential for quick ordering.

## Elevation & Depth

Visual hierarchy is established through a combination of **Tonal Layers** and **Low-Contrast Outlines**.

1.  **Base Surface:** The Warm Cream (#F5EBDD) background acts as the foundation.
2.  **Container Level:** Cards and modal surfaces use white (#FFFFFF) to create a clear "pop" against the cream base.
3.  **Shadow Character:** Shadows are used sparingly to indicate interactivity. They are highly diffused and soft: `0px 4px 12px rgba(26, 26, 26, 0.05)`. 
4.  **Structural Borders:** To maintain a clean, "Safe" utility look, 1px outlines in either `#1A1A1A` (at low opacity) or status-specific colors are used for secondary buttons and inputs, ensuring clarity without the clutter of heavy shadows.

## Shapes

The system uses a **Rounded (Level 2)** shape language to soften the industrial nature of the Inter typeface.

- **Components:** Standard elements like buttons, input fields, and small chips use a **12px** radius.
- **Containers:** Larger elements such as product cards and bottom sheets use a **16px** radius.
- **Philosophy:** This level of roundedness creates a friendly, approachable feel while remaining structured enough for a professional business application. It avoids the playfulness of full pill shapes in favor of a modern, "squircle" aesthetic.

## Components

### Buttons
- **Primary:** Solid `#1A1A1A` with white text. 12px corner radius. Used for "Add to Cart" or "Confirm."
- **Secondary:** Transparent with a 1px `#1A1A1A` border. Used for "Cancel" or "Edit."
- **Accent:** Solid `#C1642B` with white text, used specifically for promotional items or the final "Pay" action.

### Status Badges
- **Sudah Dibayar:** Solid `#1A1A1A` background, white text.
- **Sedang Diproses:** Solid `#F59E0B` background, white text.
- **Selesai:** Solid `#10B981` background, white text.
- **Belum Dibayar:** 1px border of `#6B6B6B`, text in `#6B6B6B`.

### Cards (Product & Order)
- White (#FFFFFF) background with 16px corner radius. Subtle shadow. Product cards use the secondary text color (`#6B6B6B`) for descriptions and bold primary color for pricing.

### Input Fields
- White background, 12px radius, 1px border of `#1A1A1A` at 10% opacity. Labels are always visible above the field in `#6B6B6B`.

### Navigation
- **Customer Tab Bar:** Beranda | Menu | Keranjang | Status. High-contrast active states using `#1A1A1A`.
- **Cashier Tab Bar:** Pindai | Pesanan Masuk | Riwayat. Optimized for utility with larger touch targets.
- **Design:** Fixed bottom navigation with a white background and a subtle top border or shadow to separate it from the content scroll.