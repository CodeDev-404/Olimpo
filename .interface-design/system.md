# OLIMPO — Design System

## Palette

- **ink** (base): 50 → 950 (gris pizarra)
- **ember** (accent): 50 → 600 (ámbar)

### Semantics
- Danger: red-600
- Success: green-600
- Warning: ember-500
- Info: blue-600
- Muted: ink-400 / ink-500

## Typography

- Font: Figtree (system sans-serif fallback)
- Scale: 10px (label) / 11px (uppercase label) / 12px (small) / 14px (body) / 16px (h4) / 24px (stat value)

## Spacing

- Card padding: 16px (card-body)
- Table cells: px-3 py-2.5
- Between cards: 24px
- Modal: px-6 py-4

## Component Patterns

### Sidebar
- Width: 240px
- Bg: ink-950
- Nav item: 40px height, rounded-md, gap-3
- Active: ember-500/10 bg, ember-600 text
- Inactive: ink-400 text, hover white/5 bg
- Logo: 28px ember-500 square with "O"

### Header
- Height: 56px
- Right side: date/time + logout btn

### Stat Cards
- Bg: white, border-l-[3px] color-coded
- Value: 24px bold tabular-nums
- Label: 11px uppercase tracking-wider

### Tables
- Full width, thead uppercase 11px, tbody 14px
- Row hover: ink-50
- Selected: ink-50 bg

### Buttons
- Height: 28px (sm) / default ~34px
- Default: ink-800, hover ink-900
- Active: scale(0.97)

### Badges
- 11px semibold, rounded-md, px-2 py-0.5

### Modals
- Overlay: bg-black/50
- Card: white, rounded-xl, max-w-lg, shadow-lg
- Transition: opacity + scale-95 → 100

### Empty State
- Center aligned text, ink-400
- Fallback: "No hay registros"

### Alerts / Toasts
- Fixed bottom-right, 5s auto-dismiss
- Type: success (green), warning (amber), error (red)
- Transition: translate-y + opacity
