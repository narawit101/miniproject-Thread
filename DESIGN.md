# DPI Forum Design System — Forest Green Theme

This document specifies the design system standards for the DPI Forum project, used to align and implement all CSS stylesheet layouts.

> [!IMPORTANT]
> ### 🚨 UI & Design Rules
> Always read and consult [DESIGN.md](file:///c:/D/thread/miniproject-Thread/DESIGN.md) whenever you are building new page layouts, UI views, or editing UI components and CSS stylesheets to maintain visual consistency.
> 
> **Workflow Step:** Always refer to [AGENT.md](file:///c:/D/thread/miniproject-Thread/AGENT.md) as the root developer guide and coordinator for all tasks.

---

## 🎨 1. Color Palette

The primary design theme is **Forest Green & Soft Lime**, providing a premium, fresh, and highly readable look.

| Color Role | Hex Code | Usage Example |
|---|---|---|
| **Primary** | `#123524` | Primary font color for emphasis, border color on input focus, main background gradient starting color, confirmation popups. |
| **Secondary** | `#3E7B27` | General font color, default input borders, general icons. |
| **Accent** | `#85A947` | Accent color for notifications, main body gradient background. |
| **Canvas/Light** | `#F4FFC3` | Form background (Login/Register cards), button text on hover, card box-shadow color. |
| **Danger** | `#e53e3e` | Delete thread/comment buttons, delete confirmation action button. |

---

## 🔤 2. Typography

We use the **Kanit** font family from Google Fonts for all typography to provide a modern and clean display, especially for Thai text rendering.

- **Font Family:** `'Kanit', sans-serif;`
- **Font Weights:**
  - Light: `300` (Supplemental explanations, subtexts)
  - Regular: `400` (Body copy, general paragraphs)
  - Semi-Bold: `500` (Card headers)
  - Bold: `600` / `700` (Large titles, form names)

---

## 🧱 3. Component Specs

### 3.1 Input Fields
- **Default Style:** Transparent background (`transparent`), no left, right, or top borders; has only a bottom border of `2px` with the secondary color `#3E7B27`.
- **Response (Focus/Valid State):**
  - Bottom border transitions to the primary color `#123524` with a duration of `0.5s`.
  - The label slides upwards, shrinks in size, and changes its color to the primary color `#123524`.

### 3.2 Buttons
- **Normal Style:** Pill shape (`border-radius: 40px`), `2px` border with the primary color `#123524`, text color using soft lime `#F4FFC3`.
- **Hover Effect:** Implements a sliding background gradient using a pseudo-element (`::before`) transitioning from dark green to medium green, providing a smooth dimensional and interactive feel (`transition: 0.5s`).

### 3.3 Top Navigation Bar
- Flexbox layout distributing items to the far left and right.
- Menu items feature an underline sliding effect that expands from the center when hovered (`::before` / `::after` transitions).
- Perfect circle profile picture (`border-radius: 100%`) accompanied by a dropdown card with a slide-down opening effect.

### 3.4 Cards & Grid Layout
- Main layout is built using a CSS Grid layout split into 8 sub-columns (`repeat(8, 1fr)`) to structure the priority of the Sidebar Announcements and Thread Feed content.
- Comment boxes and list item cards are styled with a white or light-gray background and slightly rounded corners for a clean, structured appearance.
