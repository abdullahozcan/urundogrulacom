---
name: Verified Trust Protocol
colors:
  surface: '#f8f9ff'
  surface-dim: '#d8dae0'
  surface-bright: '#f8f9ff'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#f2f3f9'
  surface-container: '#eceef3'
  surface-container-high: '#e7e8ee'
  surface-container-highest: '#e1e2e8'
  on-surface: '#191c20'
  on-surface-variant: '#444655'
  inverse-surface: '#2e3135'
  inverse-on-surface: '#eff0f6'
  outline: '#757687'
  outline-variant: '#c5c5d8'
  surface-tint: '#2f4de0'
  primary: '#001f90'
  on-primary: '#ffffff'
  primary-container: '#002fc9'
  on-primary-container: '#a4b0ff'
  inverse-primary: '#bac3ff'
  secondary: '#006d34'
  on-secondary: '#ffffff'
  secondary-container: '#59fd90'
  on-secondary-container: '#007236'
  tertiary: '#223148'
  on-tertiary: '#ffffff'
  tertiary-container: '#394760'
  on-tertiary-container: '#a7b5d3'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#dee0ff'
  primary-fixed-dim: '#bac3ff'
  on-primary-fixed: '#00105b'
  on-primary-fixed-variant: '#002fc9'
  secondary-fixed: '#63ff94'
  secondary-fixed-dim: '#37e279'
  on-secondary-fixed: '#00210b'
  on-secondary-fixed-variant: '#005225'
  tertiary-fixed: '#d6e3ff'
  tertiary-fixed-dim: '#b8c7e5'
  on-tertiary-fixed: '#0c1c33'
  on-tertiary-fixed-variant: '#394760'
  background: '#f8f9ff'
  on-background: '#191c20'
  surface-variant: '#e1e2e8'
  trust-blue: '#002FC9'
  success-green: '#00CC66'
  deep-navy: '#0E1D34'
  surface-ice: '#F8F9FF'
  status-error: '#D32F2F'
  status-warning: '#ED6C02'
typography:
  headline-lg:
    fontFamily: Inter
    fontSize: 40px
    fontWeight: '700'
    lineHeight: 48px
    letterSpacing: -0.02em
  headline-lg-mobile:
    fontFamily: Inter
    fontSize: 30px
    fontWeight: '700'
    lineHeight: 36px
    letterSpacing: -0.01em
  headline-md:
    fontFamily: Inter
    fontSize: 24px
    fontWeight: '600'
    lineHeight: 32px
  headline-sm:
    fontFamily: Inter
    fontSize: 20px
    fontWeight: '600'
    lineHeight: 28px
  body-lg:
    fontFamily: Inter
    fontSize: 18px
    fontWeight: '400'
    lineHeight: 28px
  body-md:
    fontFamily: Inter
    fontSize: 16px
    fontWeight: '400'
    lineHeight: 24px
  body-sm:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: '400'
    lineHeight: 20px
  label-caps:
    fontFamily: Inter
    fontSize: 12px
    fontWeight: '700'
    lineHeight: 16px
    letterSpacing: 0.05em
  code-data:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: '500'
    lineHeight: 20px
    letterSpacing: 0.02em
rounded:
  sm: 0.125rem
  DEFAULT: 0.25rem
  md: 0.375rem
  lg: 0.5rem
  xl: 0.75rem
  full: 9999px
spacing:
  container-max-width: 1280px
  gutter: 24px
  margin-desktop: 48px
  margin-mobile: 16px
  unit-xs: 4px
  unit-sm: 8px
  unit-md: 16px
  unit-lg: 24px
  unit-xl: 48px
---

## Brand & Style

The design system is engineered for a product verification service where authority and reliability are paramount. The brand personality is institutional yet technologically advanced, evoking a sense of "certified security." It targets manufacturers, retailers, and discerning consumers who require immediate, unquestionable proof of authenticity.

The design style is **Corporate / Modern** with a focus on high-clarity information architecture. It utilizes a systematic approach to whitespace and structural alignment to ensure that complex data—like manufacturing origins and certification logs—remains legible and stress-free. The visual language avoids decorative flourishes in favor of functional precision, using subtle tonal shifts and crisp borders to define hierarchy.

## Colors

The palette is anchored by **Trust Blue** (#002FC9), a high-vibrancy blue that signifies technology and corporate stability. **Success Green** is utilized exclusively for positive verification states and "pass" indicators, providing a clear psychological reward for valid scans. 

**Deep Navy** (#0E1D34) provides high-contrast grounding for primary text and navigation elements. The background strategy uses **Surface Ice** (#F8F9FF) for large structural areas to reduce eye strain, while pure white (#FFFFFF) is reserved for content cards and data containers to create a "lifted" appearance without relying on heavy shadows.

## Typography

This design system uses **Inter** exclusively to maintain a utilitarian, highly legible aesthetic across all touchpoints. The typographic hierarchy is strictly enforced to guide the user's eye from the high-level status (Headlines) to the granular product details (Body and Data).

- **Headlines:** Use tight letter-spacing and bold weights to convey authority.
- **Data Display:** Product serial numbers and technical specs should use the `code-data` style to distinguish them from descriptive prose.
- **Labels:** Uppercase labels are used for metadata categories within document previews and data tables.

## Layout & Spacing

The layout follows a **Fixed Grid** model on desktop (12 columns) and a fluid model on mobile (4 columns). The philosophy is "Information First," utilizing generous padding within cards to ensure data doesn't feel crowded.

- **Data Tables:** Should span the full width of their container, using `unit-md` for vertical cell padding to maintain high readability.
- **Breakpoints:** Mobile (<768px), Tablet (768px-1024px), Desktop (>1024px).
- **Alignment:** All content blocks must align to a hard 8px grid to maintain the "engineered" feel of the service.

## Elevation & Depth

Hierarchy is achieved through **Tonal Layers** rather than aggressive shadows. 

1. **Level 0 (Background):** `Surface Ice` (#F8F9FF).
2. **Level 1 (Cards/Tables):** Pure White (#FFFFFF) with a 1px border of `Deep Navy` at 10% opacity.
3. **Level 2 (Popovers/Modals):** Pure White with a subtle, diffused ambient shadow (0px 4px 20px rgba(14, 29, 52, 0.08)).

Verification badges use "inner-glow" styles (low-opacity tint of Success Green) to appear slightly recessed, like a physical stamp or seal on a document.

## Shapes

The design system utilizes a **Soft** (Level 1) shape language. The subtle 4px corner radius strikes a balance between the friendliness of a consumer app and the rigidity of a corporate security tool.

- **Primary Buttons:** 4px radius.
- **Status Chips:** 100px (Pill-shaped) to clearly distinguish them as interactive or status-indicating elements.
- **Document Previews:** 8px radius (`rounded-lg`) to give them a distinct physical "paper" presence.

## Components

### Buttons
- **Primary:** Trust Blue background, White text. High-contrast, no gradient.
- **Secondary:** Ghost style with 1px Trust Blue border and Blue text.
- **Success:** Only used for "Download Certificate" or "Verify" actions.

### Status Indicators
Verification status must be clear. Use a pill-shaped chip with a 10% opacity background of the status color and a 100% opacity text color (e.g., Light green background with Success Green text).

### Data Tables
Tables are the core of the product info experience.
- **Header:** Deep Navy text, bold, with a 2px Trust Blue bottom border.
- **Rows:** Alternating subtle background tints (Zebra striping) using Surface Ice.

### Document Preview Cards
Used for viewing certificates or lab reports. These should feature a thumbnail on the left, primary document title in `headline-sm`, and metadata in `label-caps`. The card should have a 1px border that darkens on hover to indicate interactivity.

### Input Fields
Strictly rectangular with a 1px border. On focus, the border thickness increases to 2px in Trust Blue. Labels should always be visible above the field (never floating).