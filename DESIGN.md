# The Architectural Truth: Design System Documentation

## 1. Overview & Creative North Star
The mission of this design system is to move beyond simple "product verification" and transition into the realm of **The Digital Notary**. We are building a high-end, editorial experience that feels as authoritative as a Swiss bank and as precise as a modern laboratory.

**Creative North Star: "The Architectural Truth"**
This system rejects the "template" look of standard SaaS products. Instead, it utilizes **The Architectural Truth**—a philosophy where hierarchy is defined by structural depth and tonal shifts rather than lines. We use aggressive typography scales, intentional asymmetry, and "glass" layers to create a UI that feels built, not just drawn. It provides the user with an immediate sense of "Absolute Originality."

---

## 2. Colors: Tonal Depth & The No-Line Rule
Our palette is anchored in a deep, authoritative navy (`primary_container`: #0e1d34) and a high-energy verification green (`tertiary_fixed_dim`: #4edea3). 

### The "No-Line" Rule
To maintain a premium, editorial aesthetic, **1px solid borders are strictly prohibited for sectioning.** 
*   **The Rule:** Boundaries must be defined solely through background color shifts.
*   **Execution:** A `surface_container_low` (#eff4ff) section should sit directly against a `surface` (#f8f9ff) background. This creates a soft, sophisticated transition that feels more modern and "open."

### Surface Hierarchy & Nesting
Treat the UI as a series of physical layers. 
*   **Base:** `surface` (#f8f9ff)
*   **Nesting:** Place a `surface_container_lowest` (#ffffff) card inside a `surface_container_low` (#eff4ff) section. This "stacked paper" approach creates natural depth without visual noise.

### The Glass & Gradient Rule
Floating elements (like navigation bars or verification modals) must utilize **Glassmorphism**.
*   **Token Use:** Use `surface_container_lowest` at 80% opacity with a `backdrop-blur` of 20px. 
*   **Signature Textures:** For primary CTAs, use a subtle linear gradient from `secondary_container` (#0e42ff) to `secondary` (#002fc9). This provides a "liquid" technological feel that flat color lacks.

---

## 3. Typography: Editorial Authority
We utilize **Inter** across all scales. The key to the premium look is the extreme contrast between `display` sizes and `label` sizes.

*   **Display Scales (lg/md):** Use these for high-impact brand moments. Tighten letter-spacing by -0.02em to give it a "custom-set" editorial feel.
*   **Headline & Title:** These are your "Statement" tiers. They convey the result of a verification (e.g., "Authenticity Confirmed").
*   **Body & Labels:** Use `on_surface_variant` (#44474d) for body text to reduce harsh contrast and increase the "luxury" feel. Labels should always be in `label-md` or `label-sm`, occasionally all-caps with +0.05em tracking for a technical, "data-stamped" look.

---

## 4. Elevation & Depth: Tonal Layering
Traditional drop shadows are often messy. In this system, we achieve "lift" through color science.

*   **The Layering Principle:** Depth is achieved by "stacking" surface tiers. To make a card feel interactive, move it from `surface_container_low` to `surface_container_highest` on hover.
*   **Ambient Shadows:** If a shadow is required for a floating modal, it must be an "Ambient Shadow." Use a blur of 40px, 0px offset, and a color of `on_background` at 5% opacity. It should feel like a soft glow of darkness, not a hard shadow.
*   **The Ghost Border Fallback:** If a container requires a border for accessibility, use a "Ghost Border": `outline_variant` (#c5c6ce) at 15% opacity. Never use 100% opacity.

---

## 5. Components

### The Verification Button (Primary)
*   **Style:** No border. Gradient fill (`secondary_container` to `secondary`). 
*   **Corner Radius:** `xl` (0.75rem) for a modern, approachable feel.
*   **Interaction:** On hover, a slight `surface_container_highest` ambient shadow emerges.

### Input Fields
*   **Style:** Forgo the traditional box. Use a `surface_container_low` background with a `surface_container_highest` bottom-only highlight (2px) when focused.
*   **Label:** Always use `label-md` floating above the input, never inside.

### Verification Cards
*   **Concept:** These should look like digital certificates. 
*   **Execution:** Use `surface_container_lowest`. No dividers. Use `body-sm` for metadata and `title-lg` for the product name. 
*   **Status Indicator:** Use a `tertiary_container` (#002214) pill with `tertiary_fixed_dim` (#4edea3) text for "Verified" states.

### Interactive Chips
*   **Style:** Use `surface_container_high`. When selected, transition to `secondary` with `on_secondary` text. No borders.

### Lists
*   **Constraint:** **Dividers are forbidden.** Separate list items using 16px of vertical white space or by alternating background tints (`surface` vs `surface_container_low`).

---

## 6. Do’s and Don’ts

### Do:
*   **Do** use asymmetrical layouts. A hero image that overlaps a `surface_container` section creates a bespoke, non-template feel.
*   **Do** use `on_primary_container` (#7785a1) for secondary text within dark navy sections to maintain readability without high-contrast strain.
*   **Do** lean into white space. If you think there is enough space, add 20% more.

### Don’t:
*   **Don’t** use pure black (#000000). Use `primary` (#00030b) for deep tones.
*   **Don’t** use "default" system shadows. They break the Architectural Truth.
*   **Don’t** use sharp corners. Use the `xl` (0.75rem) or `lg` (0.5rem) tokens to keep the "tech-humanist" balance.
*   **Don’t** use more than one vibrant color per screen. If the Green (Tertiary) is present for a success state, the Blue (Secondary) should be dialled back.

---

## 7. Signature Interaction: The "Trust Scan"
For `urundogrula.com`, every verification action should be accompanied by a subtle "scanning" animation: a 10% opacity `tertiary_fixed` (#6ffbbe) gradient sweep across the card. This reinforces the technological nature of the product and rewards the user for their engagement.