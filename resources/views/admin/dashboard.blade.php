@extends('admin.layouts.app')

<x-assets.tabulator />

@push('page-css')
    <link rel="stylesheet" href="{{asset('assets/plugins/chart.js/Chart.min.css')}}">
    <style>
        /*
        ╔════════════════════════════════════════════════════════════════════════════════╗
        ║  DASHBOARD CUSTOMIZATION - NO LAYOUT SHIFT BETWEEN LIGHT & DARK MODE          ║
        ║  Same layout in both modes - smooth transition without eating space            ║
        ╚════════════════════════════════════════════════════════════════════════════════╝
        
        🎯 QUICK REFERENCE - Easy to Find:
        ───────────────────────────────────────────────────────────────────────────────
        
        1️⃣  RESIZE HEIGHT 📏:
           Look for: 📏 RESIZE HEIGHT - REDUCE OR INCREASE THIS
           Variables:
           • --dashboard-card-min-height: auto;     (Indicator Cards)
           • --sales-table-min-height: auto;        (Today Sales Box)
           • --resources-min-height: auto;          (Resources Chart)
           
           Options for Indicator Cards (Today Sales, Product Categories, etc.):
           ✓ auto        = Default size (no fixed height)
           ✓ 100px       = Small/compact
           ✓ 120px       = Medium
           ✓ 150px       = Large
           
           Options for Today Sales Table:
           ✓ auto        = Default size
           ✓ 200px       = Short (less table rows visible)
           ✓ 300px       = Medium
           ✓ 400px       = Tall (more table rows visible)
           
           Options for Resources Chart:
           ✓ auto        = Default size
           ✓ 250px       = Short
           ✓ 300px       = Medium
           ✓ 350px       = Tall
        
        2️⃣  LIMIT MAX HEIGHT (Optional - for scrolling) 📏:
           Look for: --dashboard-card-max-height: none;
           Variables:
           • --dashboard-card-max-height: none;     (Indicator Cards)
           • --sales-table-max-height: none;        (Today Sales Box)
           • --resources-max-height: none;          (Resources Chart)
           
           Options:
           ✓ none        = No limit
           ✓ 200px       = Set maximum height (creates scrollbar if needed)
           ✓ 300px       = More space allowed
        
        3️⃣  RESHAPE BOXES 🔲:
           Look for: 🔲 BOX SHAPE - CHANGE TO RESHAPE
           Variables:
           • --dashboard-card-border-radius: 20px;  (Indicator Cards)
           • --sales-table-border-radius: 10px;     (Today Sales Box)
           • --resources-border-radius: 10px;       (Resources Chart)
           
           Shape Options:
           ✓ 0px         = Square (no rounding)
           ✓ 5px         = Slightly rounded
           ✓ 10px        = Rounded
           ✓ 15px        = Very rounded
           ✓ 20px        = Extremely rounded
           ✓ 30px        = Almost circle-like
        
        4️⃣  REDUCE OR INCREASE BORDER THICKNESS 🔴:
           Look for: 🔴 BORDER THICKNESS - REDUCE OR INCREASE THIS
           Variables:
           • --dashboard-card-border-width: 1px;
           • --sales-table-border-width: 1px;
           • --resources-border-width: 1px;
           
           Options:
           ✓ 0px    = No border (only outline shows)
           ✓ 0.5px  = Super thin (hair line)
           ✓ 1px    = Thin (current - default)
           ✓ 2px    = Thick
           ✓ 3px    = Very thick
           
           Example to REDUCE: Change 1px → 0.5px for thinner lines
           Example to INCREASE: Change 1px → 2px for thicker lines
        
        2️⃣  MOVE BOXES IN NIGHT MODE ONLY 🎯:
           Look for: 🌙 CUSTOMIZATION VARIABLES - Dark Mode
           Only in dark mode section (body.dark-mode):
           • --sales-table-move-y: 15px;         (Today Sales box - currently 15px down)
           • --resources-move-y: 15px;           (Resources box - currently 15px down)
           
           To adjust:
           ✓ 0px    = Normal position (no move)
           ✓ 10px   = Slight move down
           ✓ 15px   = Current (moderate move down)
           ✓ 20px   = More move down
           ✓ -10px  = Move up
        
        3️⃣  CHANGE BORDER COLOR:
           Look for: --dashboard-card-border-color (and similar for other sections)
           
           Light Mode Colors (in :root):
           • Change to ANY color: #ffffff (white), #cccccc (gray), #000000 (black), etc.
           
           Dark Mode Colors (in body.dark-mode):
           • Adjust to lighter colors: rgba(255, 255, 255, 0.12) for dark backgrounds
        
        3️⃣  MOVE BOXES (Left/Right/Up/Down):
           Look for: 🎯 MOVE BOXES - CHANGE THESE TO MOVE CARDS
           --dashboard-card-move-x: 0;   (Left/Right movement)
           --dashboard-card-move-y: 0;   (Up/Down movement)
        
        4️⃣  WORKS FOR ALL THREE SECTIONS:
           ✓ Indicator Cards (Today Sales Cash, Product Categories, etc.)
           ✓ Today Sales Table
           ✓ Resources Chart
        
        5️⃣  BOTH LIGHT & DARK MODE:
           All position & thickness settings are IDENTICAL in both modes
           Only colors change to prevent layout shifting!
        ───────────────────────────────────────────────────────────────────────────────
        */

        /* ┌─────────────────────────────────────────────────────────────────────────────┐ */
        /* │ 🔧 CUSTOMIZATION VARIABLES - Light Mode (Nellavio-Inspired)               │ */
        /* └─────────────────────────────────────────────────────────────────────────────┘ */
        :root {
            /* ═══ NELLAVIO LIGHT COLOR PALETTE ═══ */
            --nellavio-primary-bg: #f7f7f7;
            --nellavio-card-bg: white;
            --nellavio-border-color: rgb(240, 240, 245);
            --nellavio-text-primary: rgba(0, 0, 0, 0.9);
            --nellavio-text-secondary: rgba(0, 0, 0, 0.6);
            --nellavio-accent: rgb(118, 167, 247);
            --nellavio-accent-hover: rgb(76, 144, 255);
            --nellavio-shadow-sm: 0 1px 2px rgba(0, 0, 0, 0.04);
            --nellavio-shadow-md: 0 4px 20px -2px rgba(15, 23, 42, 0.06), 0 1px 3px 0 rgba(15, 23, 42, 0.03);
            --nellavio-shadow-hover: 0 14px 30px -4px rgba(15, 23, 42, 0.12), 0 4px 10px -2px rgba(15, 23, 42, 0.05);
            
            /* ═══ DASHBOARD INDICATOR CARDS STYLING ═══ */
            /* 📏 BOX SIZE */
            --dashboard-card-width: 100%;
            /* 📐 RESIZE HEIGHT - REDUCE OR INCREASE THIS */
            --dashboard-card-min-height: auto;                 /* auto=default, 100px=small, 120px=medium, 150px=large */
            --dashboard-card-max-height: none;                 /* none=no limit, 200px, 250px, 300px */
            
            /* 🔲 BOX SHAPE - CHANGE TO RESHAPE */
            --dashboard-card-border-radius: 16px;             /* 0px=square, 10px=slight, 16px=modern, 20px=rounded */
            /* 🔴 BORDER THICKNESS - REDUCE OR INCREASE THIS */
            --dashboard-card-border-width: 1px;              /* 0px=none, 0.5px=super thin, 1px=thin, 2px=thick */
            
            /* 📍 SPACING - SAME FOR BOTH MODES TO PREVENT SHIFT */
            --dashboard-card-padding: 1.35rem 1.5rem;
            --dashboard-card-margin: 0;
            --dashboard-card-gap: 1.15rem;
            
            /* 🎯 MOVE BOXES - CHANGE THESE TO MOVE CARDS */
            --dashboard-card-move-x: 0;                         /* Move Left/Right: -20px=left, 0=center, 20px=right */
            --dashboard-card-move-y: 0;                         /* Move Up/Down: -10px=up, 0=normal, 10px=down */
            
            /* 🎨 COLORS - LIGHT MODE (Nellavio) */
            --dashboard-card-bg: var(--nellavio-card-bg);
            --dashboard-card-border-color: rgba(226, 232, 240, 0.85);
            --dashboard-card-border-outline: 1px solid rgba(240, 240, 245, 0.8);
            --dashboard-card-border-hover: rgba(59, 130, 246, 0.5);
            --dashboard-card-shadow: var(--nellavio-shadow-md);
            --dashboard-card-shadow-hover: var(--nellavio-shadow-hover);
            
            /* ═══ TODAY SALES TABLE CONTROLS - LIGHT MODE (Nellavio) ═══ */
            /* Find this section quickly when changing the Today Sales box. */
            /* 📏 TODAY SALES BOX SIZE - applies to both light and dark mode. */
            --sales-table-width: 100%;                         /* WIDTH: larger % = wider; smaller % = narrower. */
            --sales-table-min-height: 520px;                   /* HEIGHT: positive px increases; smaller/0px decreases. */
            --sales-table-max-height: none;                    /* Keep none so long tables expand instead of clipping. */
            /* INNER TODAY SALES TABLE: reduce width with a smaller %, or reduce height with a px limit. */
            --sales-inner-width: 100%;                          /* WIDTH: 90% = narrower, 100% = full width. */
            --sales-inner-max-height: none;                      /* HEIGHT: none or auto = AUTOMATIC (table keeps full natural height - cannot be reduced). Type a px value like 250px to FIX the height and show a scrollbar. */
            
            /* 🔲 BOX SHAPE - CHANGE TO RESHAPE */
            --sales-table-border-radius: 16px;                /* 0px=square, 10px=slight, 16px=modern, 20px=rounded */
            /* 🔴 BORDER THICKNESS - REDUCE OR INCREASE THIS */
            --sales-table-border-width: 1px;                 /* 0px=none, 0.5px=super thin, 1px=thin, 2px=thick */
            
            /* 📍 SPACING - SAME FOR BOTH MODES */
            --sales-table-padding: 1.5rem;
            --sales-table-margin: 0 0 1.5rem 0;
            
            /* 🎯 MOVE TODAY SALES BOX */
            --sales-table-move-x: 0;                            /* MOVE LEFT/RIGHT: negative=left, positive=right. */
            --sales-table-move-y: -15px;                            /* MOVE UP/DOWN: negative=up, positive=down. */
            
            /* 🎨 COLORS - LIGHT MODE (Nellavio) */
            --sales-table-bg: var(--nellavio-card-bg);
            --sales-table-border-color: rgba(226, 232, 240, 0.85);
            --sales-table-border-outline: 1px solid rgba(240, 240, 245, 0.8);
            --sales-table-border-hover: rgba(59, 130, 246, 0.5);
            --sales-table-shadow: var(--nellavio-shadow-md);
            --sales-table-shadow-hover: var(--nellavio-shadow-hover);
            
            /* ═══ RESOURCES CHART STYLING (Nellavio) ═══ */
            /*
                RESOURCE BOX VARIABLES - edit these to change the Resources card size
                - Light mode variables are declared here (use px values)
                - Dark mode overrides are in the `body.dark-mode` section below

                Quick presets:
                • Short:  --resources-min-height: 250px; --resources-chart-height: 180px;
                • Medium: --resources-min-height: 300px; --resources-chart-height: 220px;
                • Tall:   --resources-min-height: 350px; --resources-chart-height: 260px;
            */

            /* 📏 BOX SIZE & WIDTH (light mode) */
            --resources-width: 100%;

            /* 📐 RESIZE HEIGHT - set minimum and maximum height for the box (light mode) */
            --resources-min-height: 520px;    /* light mode: min height (px) */
            --resources-max-height: none;     /* allow the card to match the table height */

            /* PIE GRAPH CONTROLS (light mode) - change these values to resize or move it. */
            --resources-chart-height: 300px;  /* RESIZE: increase for a taller/larger pie area. */
            --resources-chart-max-width: 100%; /* RESIZE WIDTH: lower this to make the chart narrower. */
            --resources-chart-h-offset: 0px;   /* MOVE LEFT/RIGHT: positive = right, negative = left. */
            --resources-chart-v-offset: 60px;  /* MOVE UP/DOWN: positive = down, negative = up. */

            /* 🔲 BOX SHAPE */
            --resources-border-radius: 16px;  /* 0=sharp, 16=modern rounded, 20=very rounded */
            /* 🔴 BORDER THICKNESS */
            --resources-border-width: 1px;

            /* 📍 SPACING */
            --resources-padding: 1.5rem;
            --resources-margin: 0;

            /* 🎯 MOVE BOX - translate the box to move it left/right/up/down (light mode)
               - Use positive X to move right, negative X to move left
               - Use positive Y to move down, negative Y to move up
            */
            --resources-move-x: 0;           /* e.g. -20px = left, 20px = right */
            --resources-move-y: -15px;           /* e.g. -10px = up, 10px = down */

            /* 🎨 COLORS - LIGHT MODE (Nellavio) */
            --resources-bg: var(--nellavio-card-bg);
            --resources-border-color: rgba(226, 232, 240, 0.85);
            --resources-border-outline: 1px solid rgba(240, 240, 245, 0.8);
            --resources-border-hover: rgba(59, 130, 246, 0.5);
            --resources-shadow: var(--nellavio-shadow-md);
            --resources-shadow-hover: var(--nellavio-shadow-hover);
        }

        /* ┌─────────────────────────────────────────────────────────────────────────────┐ */
        /* │ 🌙 CUSTOMIZATION VARIABLES - Dark Mode (Nellavio-Inspired)                │ */
        /* │   Note: All position & thickness variables are SAME as Light Mode         │ */
        /* │   Only colors change - NO LAYOUT SHIFT!                                   │ */
        /* └─────────────────────────────────────────────────────────────────────────────┘ */
        body.dark-mode {
            /* ═══ NELLAVIO DARK COLOR PALETTE ═══ */
            --nellavio-dark-primary-bg: rgb(22, 26, 31);
            --nellavio-dark-card-bg: rgb(28, 32, 37);
            --nellavio-dark-border-color: rgba(255, 255, 255, 0.08);
            --nellavio-dark-text-primary: rgb(231, 233, 236);
            --nellavio-dark-text-secondary: rgb(140, 145, 150);
            --nellavio-dark-accent: rgb(61, 185, 133);
            --nellavio-dark-accent-hover: rgb(105, 217, 170);
            --nellavio-dark-shadow-sm: 0 1px 2px rgba(0, 0, 0, 0.2);
            --nellavio-dark-shadow-md: 0 4px 20px -2px rgba(0, 0, 0, 0.45);
            --nellavio-dark-shadow-hover: 0 14px 32px -4px rgba(0, 0, 0, 0.65);
            
            /* ═══ DASHBOARD CARDS - Same spacing & positioning, only colors change ═══ */
            /* 🎯 MOVE & THICKNESS SAME AS LIGHT MODE - See :root variables above */
            --dashboard-card-bg: var(--nellavio-dark-card-bg);
            --dashboard-card-border-color: var(--nellavio-dark-border-color);
            --dashboard-card-border-outline: 1px solid rgba(255, 255, 255, 0.08);
            --dashboard-card-border-hover: var(--nellavio-dark-accent);
            --dashboard-card-shadow: var(--nellavio-dark-shadow-md);
            --dashboard-card-shadow-hover: var(--nellavio-dark-shadow-hover);
            
            /* ═══ TODAY SALES TABLE CONTROLS - DARK MODE (Nellavio) ═══ */
            /* Dark mode inherits the light-mode size and padding values above. */
            /* Add a variable here to make dark mode different from light mode. */
            --sales-table-bg: var(--nellavio-dark-card-bg);
            --sales-table-border-color: var(--nellavio-dark-border-color);
            --sales-table-border-outline: 1px solid rgba(255, 255, 255, 0.08);
            /* Dark mode uses the same width and height controls as light mode above. */
            --sales-table-width: 100%;                         /* WIDTH: smaller % decreases; larger % increases. */
            --sales-table-min-height: 520px;                   /* MUST match light mode (520px) so the two boxes are identical. */
            --sales-table-max-height: none;                    /* MUST match light mode (none) so long tables expand the same way. */
            /* DARK MODE INNER TODAY SALES TABLE: separate controls from light mode. */
            --sales-inner-width: 100%;                          /* WIDTH: smaller % = narrower, 100% = full width. */
            --sales-inner-max-height: none;                    /* HEIGHT: use positive px, such as 300px; negative values are invalid. */
            --sales-table-move-x: 0;                            /* MOVE LEFT/RIGHT: negative=left, positive=right. */
            --sales-table-move-y: -15px;                         /* 0px keeps dark mode aligned with light mode. */
            --sales-table-border-hover: var(--nellavio-dark-accent);
            --sales-table-shadow: var(--nellavio-dark-shadow-md);
            --sales-table-shadow-hover: var(--nellavio-dark-shadow-hover);
            
                /* RESOURCES (Dark Mode - Nellavio) - overrides for night appearance
                    Edit these to change Resources box in dark mode (px values)
                */
                --resources-bg: var(--nellavio-dark-card-bg);
                --resources-border-color: var(--nellavio-dark-border-color);
                --resources-border-outline: 1px solid rgba(255, 255, 255, 0.08);
                /* Match light-mode height so the row spacing does not change. */
                --resources-min-height: 520px;   /* Match light mode (520px) so the box height is identical in both modes. */
                --resources-max-height: none;    /* Match light mode (none) so the box height is identical in both modes. */
                /* PIE GRAPH CONTROLS (dark mode) - update these too when dark mode is enabled. */
                --resources-chart-height: 300px;  /* RESIZE: increase for a taller/larger pie area. */
                --resources-chart-h-offset: 0px;   /* MOVE LEFT/RIGHT: positive = right, negative = left. */
                --resources-chart-v-offset: 60px;  /* MOVE UP/DOWN: positive = down, negative = up. */
                /* Keep Resources in the same position as light mode. */
                --resources-move-x: 0;
                --resources-move-y: -15px;  /* 0 keeps dark mode aligned with light mode. */
                --resources-border-hover: var(--nellavio-dark-accent);
                --resources-shadow: var(--nellavio-dark-shadow-md);
                --resources-shadow-hover: var(--nellavio-dark-shadow-hover);
        }

        /* ┌─────────────────────────────────────────────────────────────────────────────┐ */
        /* │ 🎨 LIGHT MODE - Dashboard Indicator Cards (Nellavio)                     │ */
        /* └─────────────────────────────────────────────────────────────────────────────┘ */
        .dashboard-indicators-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: var(--dashboard-card-gap);
            margin-bottom: 2rem;
        }

            .dashboard-reveal-1 { animation-delay: 0.1s; }
            .dashboard-reveal-2 { animation-delay: 0.2s; }
            .dashboard-reveal-3 { animation-delay: 0.3s; }
            .dashboard-reveal-4 { animation-delay: 0.4s; }
            .dashboard-reveal-5 { animation-delay: 0.5s; }
            .dashboard-reveal-6 { animation-delay: 0.6s; }

            @media (prefers-reduced-motion: reduce) {
                .animate__animated {
                    animation-duration: 0.01ms !important;
                    animation-iteration-count: 1 !important;
                    transition-duration: 0.01ms !important;
                }
            }

            .resources-chart-holder {
                width: 100%;
                min-height: var(--resources-chart-height);
                position: relative;
                z-index: 11;
                display: flex;
                justify-content: center;
            }

        .dashboard-card {
            /* 📏 SIZE & SHAPE - NO CHANGE BETWEEN MODES */
            width: var(--dashboard-card-width);
            /* 📏 HEIGHT RESIZE - Adjust --dashboard-card-min-height and --dashboard-card-max-height */
            min-height: var(--dashboard-card-min-height);
            max-height: var(--dashboard-card-max-height);
            border-radius: var(--dashboard-card-border-radius);
            
            /* 📍 SPACING - NO CHANGE BETWEEN MODES */
            padding: var(--dashboard-card-padding);
            margin: var(--dashboard-card-margin);
            
            /* 🎯 MOVE BOXES - Transform to position */
            transform: translateX(var(--dashboard-card-move-x)) translateY(var(--dashboard-card-move-y));
            
            /* 🎨 COLORS - CHANGE PER MODE */
            background-color: var(--dashboard-card-bg);
            /* 🔴 BORDER THICKNESS - Adjust --dashboard-card-border-width to make thinner/thicker */
            border: var(--dashboard-card-border-width) solid var(--dashboard-card-border-color);
            /* Subtle outline when border is very thin (0.5px or less) */
            outline: var(--dashboard-card-border-outline);
            outline-offset: -1px;
            box-shadow: var(--dashboard-card-shadow);
            
            /* ⏱️ SMOOTH TRANSITION WITHOUT JUMP */
            transition: border-color 0.3s ease, box-shadow 0.3s ease, background-color 0.3s ease, border-color 0.3s ease;
        }

        .dashboard-card:hover,
        .dashboard-card:focus-within {
            border-color: var(--dashboard-card-border-hover);
            box-shadow: var(--dashboard-card-shadow-hover);
        }

        /* ┌─────────────────────────────────────────────────────────────────────────────┐ */
        /* │ 🎨 LIGHT MODE - Today Sales Container                                     │ */
        /* └─────────────────────────────────────────────────────────────────────────────┘ */
        .card-table {
            /* 📏 SIZE & SHAPE - NO CHANGE BETWEEN MODES */
            width: var(--sales-table-width);
            /* 📏 HEIGHT RESIZE - Adjust --sales-table-min-height and --sales-table-max-height */
            min-height: var(--sales-table-min-height);
            max-height: var(--sales-table-max-height);
            border-radius: var(--sales-table-border-radius);
            
            /* 📍 SPACING - NO CHANGE BETWEEN MODES */
            padding: var(--sales-table-padding);
            margin: var(--sales-table-margin);
            
            /* 🎯 MOVE BOX - Keep vertical movement independent of entrance animations. */
            position: relative;
            top: var(--sales-table-move-y);
            transform: translateX(var(--sales-table-move-x));
            
            /* 🎨 COLORS - CHANGE PER MODE */
            background-color: var(--sales-table-bg);
            /* 🔴 BORDER THICKNESS - Adjust --sales-table-border-width to make thinner/thicker */
            border: var(--sales-table-border-width) solid var(--sales-table-border-color);
            /* Subtle outline when border is very thin (0.5px or less) */
            outline: var(--sales-table-border-outline);
            outline-offset: -1px;
            box-shadow: var(--sales-table-shadow);
            
            /* ⏱️ SMOOTH TRANSITION WITHOUT JUMP */
            transition: border-color 0.3s ease, box-shadow 0.3s ease, background-color 0.3s ease, border-color 0.3s ease;
        }

        .card-table:hover,
        .card-table:focus-within {
            border-color: var(--sales-table-border-hover);
            box-shadow: var(--sales-table-shadow-hover);
        }

        .dashboard-sales-resources-row {
            align-items: stretch;
        }
        .dashboard-sales-resources-row > [class*="col-"] {
            display: flex;
        }
        .dashboard-sales-resources-row > [class*="col-"] > .card {
            /* The outer box adopts the inner table/chart height in both themes. */
            height: auto;
            width: 100%;
        }
        .dashboard-card-animation {
            animation-fill-mode: none;
        }

        #sales-table_wrapper,
        #sales-table_wrapper .table-responsive,
        #sales-table {
            width: var(--sales-inner-width) !important;
        }

        /* INNER TODAY SALES TABLE SIZE: this changes the table area, not the outer card position.
           NOTE: 'height' (not just max-height) makes this a FIXED size, so reducing the value below
           the natural table height always shrinks the area and shows the scrollbar. The outer card
           box has its own min-height (--sales-table-min-height) and the row stretches columns to
           equal height, so the outer box height is independent of this setting. */
        #sales-table_wrapper .table-responsive {
            /* Set --sales-inner-max-height to a px value (e.g. 250px) to FIX the table
               height with a scrollbar. Use none/auto for automatic height. */
            height: var(--sales-inner-max-height, auto);
            max-height: var(--sales-inner-max-height, none);
            overflow-y: auto;
        }

        body:not(.dark-mode) .page-wrapper #sales-table .sale-product-detail-link {
            color: #1677ff !important;
            font-size: inherit;
            text-decoration: none;
        }
        body:not(.dark-mode) .page-wrapper #sales-table .sale-product-detail-link:hover,
        body:not(.dark-mode) .page-wrapper #sales-table .sale-product-detail-link:focus {
            color: #0b5ed7 !important;
            text-decoration: underline;
        }
        body.dark-mode .page-wrapper #sales-table .sale-product-detail-link {
            color: rgb(61, 185, 133) !important;
        }
        body.dark-mode .page-wrapper #sales-table .sale-product-detail-link:hover,
        body.dark-mode .page-wrapper #sales-table .sale-product-detail-link:focus {
            color: rgb(105, 217, 170) !important;
        }
        #dashboardProductDetailsModal .modal-dialog {
            max-width: 760px;
        }
        #dashboardProductDetailsModal .dashboard-product-details-layout {
            align-items: stretch;
            display: grid;
            gap: 24px;
            grid-template-columns: minmax(220px, .9fr) minmax(0, 1.35fr);
        }
        #dashboardProductDetailsModal .dashboard-product-details-image {
            align-items: center;
            background: #f5f7fb;
            border: 1px solid #e5eaf1;
            border-radius: 8px;
            display: flex;
            justify-content: center;
            min-height: 300px;
            padding: 18px;
        }
        #dashboardProductDetailsModal .dashboard-product-details-image img {
            border-radius: 6px;
            max-height: 300px;
            object-fit: contain;
            width: 100%;
        }
        #dashboardProductDetailsModal .dashboard-product-details-info {
            align-content: center;
            display: grid;
            gap: 12px 18px;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        #dashboardProductDetailsModal .dashboard-product-details-info > div {
            border-bottom: 1px solid #edf0f4;
            padding-bottom: 8px;
            min-width: 0;
            position: relative;
        }
        #dashboardProductDetailsModal .dashboard-product-details-info strong {
            color: #7a8795;
            display: block;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 3px;
            text-transform: uppercase;
        }
        #dashboardProductDetailsModal .dashboard-product-details-info span {
            color: #263238;
            word-break: break-word;
            overflow-wrap: anywhere;
        }
        #dashboardProductDetailsModal .product-detail-value-wrap {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 6px;
            min-width: 0;
            position: relative;
        }
        #dashboardProductDetailsModal .dashboard-product-detail.product,
        #dashboardProductDetailsModal .product-detail-value-wrap .dashboard-product-detail.product,
        #dashboardProductDetailsModal .product {
            min-width: 0;
            flex: 1 1 auto;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            display: block;
            border: 0 !important;
            border-radius: 0 !important;
            padding: 0 !important;
            background: transparent !important;
            background-color: transparent !important;
            margin: 0 !important;
            margin-bottom: 0 !important;
            box-shadow: none !important;
            transition: none !important;
        }
        #dashboardProductDetailsModal .product-info-btn {
            flex: 0 0 auto;
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 1.5rem;
            height: 1.5rem;
            padding: 0;
            border: 0;
            background: transparent;
            color: #0f766e;
            cursor: help;
            font-size: 15px;
        }
        #dashboardProductDetailsModal .product-info-btn:hover,
        #dashboardProductDetailsModal .product-info-btn:focus {
            color: #0d9488;
        }
        #dashboardProductDetailsModal .product-info-btn::after {
            content: attr(data-tooltip);
            position: absolute;
            right: 0;
            bottom: calc(100% + 8px);
            width: 220px;
            max-height: 180px;
            overflow-y: auto;
            padding: 8px 10px;
            border-radius: 5px;
            background: #1f2937;
            color: #fff;
            font-size: 12px;
            line-height: 1.35;
            text-align: left;
            white-space: pre-wrap;
            word-break: break-all;
            overflow-wrap: anywhere;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.25);
            z-index: 1060;
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transition: opacity .15s ease, visibility .15s ease;
        }
        #dashboardProductDetailsModal .product-info-btn:hover::after,
        #dashboardProductDetailsModal .product-info-btn:focus::after {
            opacity: 1;
            visibility: visible;
        }
        body.dark-mode #dashboardProductDetailsModal .product-info-btn {
            color: #14b8a6;
        }
        body.dark-mode #dashboardProductDetailsModal .product-info-btn:hover,
        body.dark-mode #dashboardProductDetailsModal .product-info-btn:focus {
            color: #2dd4bf;
        }
        body.dark-mode #dashboardProductDetailsModal .product-info-btn::after {
            background: #0f172a;
            border: 1px solid #334155;
            color: #f8fafc;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.5);
        }
        body.dark-mode #dashboardProductDetailsModal .product {
            background: transparent !important;
            background-color: transparent !important;
            border: 0 !important;
        }
        @media (max-width: 576px) {
            #dashboardProductDetailsModal .dashboard-product-details-layout,
            #dashboardProductDetailsModal .dashboard-product-details-info {
                grid-template-columns: 1fr;
            }
            #dashboardProductDetailsModal .dashboard-product-details-image {
                min-height: 220px;
            }
        }

        /* ┌─────────────────────────────────────────────────────────────────────────────┐ */
        /* │ 🎨 LIGHT MODE - Resources Chart Container                                 │ */
        /* └─────────────────────────────────────────────────────────────────────────────┘ */
        .card-chart {
            /* 📏 SIZE & SHAPE - NO CHANGE BETWEEN MODES */
            width: var(--resources-width);
            /* 📏 HEIGHT RESIZE - Adjust --resources-min-height and --resources-max-height */
            min-height: var(--resources-min-height);
            max-height: var(--resources-max-height);
            border-radius: var(--resources-border-radius);
            
            /* 📍 SPACING - NO CHANGE BETWEEN MODES */
            padding: var(--resources-padding);
            margin: var(--resources-margin);
            
            /* 🎯 MOVE BOX - Keep vertical movement independent of entrance animations. */
            position: relative;
            top: var(--resources-move-y);
            transform: translateX(var(--resources-move-x));
            
            /* 🎨 COLORS - CHANGE PER MODE */
            background-color: var(--resources-bg);
            /* 🔴 BORDER THICKNESS - Adjust --resources-border-width to make thinner/thicker */
            border: var(--resources-border-width) solid var(--resources-border-color);
            /* Subtle outline when border is very thin (0.5px or less) */
            outline: var(--resources-border-outline);
            outline-offset: -1px;
            box-shadow: var(--resources-shadow);
            
            /* ⏱️ SMOOTH TRANSITION WITHOUT JUMP */
            transition: border-color 0.3s ease, box-shadow 0.3s ease, background-color 0.3s ease, border-color 0.3s ease;
        }

        .resources-chart-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            transform: translate(var(--resources-chart-h-offset), var(--resources-chart-v-offset));
            width: 100%;
            min-height: var(--resources-chart-height);
            opacity: 1 !important;
            visibility: visible !important;
            position: relative;
            z-index: 10;
        }

        .resources-chart-wrapper canvas {
            display: block !important;
            width: 100% !important;
            height: 100% !important;
            max-width: var(--resources-chart-max-width) !important;
            height: var(--resources-chart-height) !important;
            margin: 0 auto !important;
            opacity: 1 !important;
            visibility: visible !important;
            position: relative;
            z-index: 11;
        }
        
        .resources-chart-wrapper .chartjs-render-monitor {
            display: none !important;
        }
        
        .resources-chart-wrapper .chart-container {
            display: none !important;
        }
        
        /* Override Chart.js inline styles that hide the canvas */
        #pieChart {
            display: block !important;
            width: 100% !important;
            max-width: 100% !important;
            height: var(--resources-chart-height) !important;
            margin: 0 auto !important;
            opacity: 1 !important;
            visibility: visible !important;
        }

        .resources-chart-wrapper {
            position: relative;
        }

        .resources-chart-processing,
        .resources-chart-empty {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            min-height: var(--resources-chart-height);
            width: 100%;
            color: #6c757d;
        }

        .resources-chart-spinner {
            width: 36px;
            height: 36px;
            margin-bottom: 12px;
            border: 4px solid #e9ecef;
            border-top-color: #36A2EB;
            border-radius: 50%;
            animation: resources-chart-spin 0.8s linear infinite;
        }

        @keyframes resources-chart-spin {
            to { transform: rotate(360deg); }
        }

        .card-chart:hover,
        .card-chart:focus-within {
            border-color: var(--resources-border-hover);
            box-shadow: var(--resources-shadow-hover);
        }

        /* ╔═════════════════════════════════════════════════════════════════════════════╗ */
        /* ║  Dashboard Layout & Background (Nellavio Modern)                          ║ */
        /* ╚═════════════════════════════════════════════════════════════════════════════╝ */
        
        /* Dashboard page background */
        .dashboard-wrapper, .content-wrapper {
            background-color: var(--nellavio-primary-bg);
        }
        
        body.dark-mode .dashboard-wrapper,
        body.dark-mode .content-wrapper {
            background-color: rgb(22, 26, 31);
        }
        
        /* Main container spacing */
        .container-fluid {
            padding: 2rem 1.5rem;
        }

        .dashboard-hero {
            background: linear-gradient(135deg, #1e40af 0%, #2563eb 50%, #3b82f6 100%);
            border-radius: 16px;
            color: #fff;
            padding: 2.25rem 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 28px -4px rgba(37, 99, 235, 0.25);
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.15);
        }
        
        .dashboard-hero::before {
            content: '';
            position: absolute;
            top: -60px;
            right: -60px;
            width: 240px;
            height: 240px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.18) 0%, rgba(255, 255, 255, 0) 70%);
            border-radius: 50%;
            pointer-events: none;
        }

        .dashboard-hero::after {
            content: '';
            position: absolute;
            bottom: -50px;
            left: 20%;
            width: 220px;
            height: 220px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0) 70%);
            border-radius: 50%;
            pointer-events: none;
        }

        .hero-live-dot {
            display: inline-block;
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background-color: #34d399;
            box-shadow: 0 0 0 2px rgba(52, 211, 153, 0.4);
            animation: hero-dot-pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        @keyframes hero-dot-pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.6; transform: scale(1.15); }
        }
        
        .dashboard-hero h3 {
            font-size: 1.85rem;
            margin-bottom: 0.4rem;
            font-weight: 700;
            letter-spacing: -0.02em;
            position: relative;
            z-index: 1;
        }
        
        .dashboard-hero p {
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 0;
            font-size: 0.95rem;
            position: relative;
            z-index: 1;
        }
        
        body.dark-mode .dashboard-hero {
            background: linear-gradient(135deg, rgb(16, 75, 54) 0%, rgb(13, 148, 136) 50%, rgb(5, 150, 105) 100%);
            box-shadow: 0 10px 28px -4px rgba(16, 185, 129, 0.25);
            border-color: rgba(255, 255, 255, 0.1);
        }

        /* ═══ MODERN METRIC CARDS ═══ */
        .dashboard-card {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .dashboard-card .card-body {
            padding: 1.35rem 1.4rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 100%;
        }

        .dashboard-card .dash-widget-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 0.85rem;
        }

        .dashboard-card .dash-widget-icon {
            width: 46px;
            height: 46px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 13px;
            font-size: 1.25rem;
            color: #fff;
            flex-shrink: 0;
            transition: transform 0.25s ease;
        }

        .dashboard-card:hover .dash-widget-icon {
            transform: scale(1.08);
        }

        .dashboard-card .dash-widget-icon.icon-sales {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            box-shadow: 0 4px 14px rgba(16, 185, 129, 0.32);
        }

        .dashboard-card .dash-widget-icon.icon-categories {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            box-shadow: 0 4px 14px rgba(37, 99, 235, 0.32);
        }

        .dashboard-card .dash-widget-icon.icon-expired {
            background: linear-gradient(135deg, #f43f5e 0%, #e11d48 100%);
            box-shadow: 0 4px 14px rgba(244, 63, 94, 0.32);
        }

        .dashboard-card .dash-widget-icon.icon-barcode {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            box-shadow: 0 4px 14px rgba(139, 92, 246, 0.32);
        }

        .dashboard-card .dash-count-val {
            font-size: 1.85rem;
            font-weight: 800;
            letter-spacing: -0.03em;
            margin: 0;
            line-height: 1.15;
            color: #1e293b;
        }

        body.dark-mode .dashboard-card .dash-count-val {
            color: rgb(241, 245, 249);
        }

        .dashboard-card .currency-sym {
            font-size: 0.95rem;
            font-weight: 600;
            opacity: 0.55;
            margin-right: 3px;
        }

        .dashboard-card .dash-widget-footer {
            border-top: 1px solid rgba(226, 232, 240, 0.7);
            padding-top: 0.65rem;
            margin-top: 0.65rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 0.8125rem;
            color: #64748b;
        }

        body.dark-mode .dashboard-card .dash-widget-footer {
            border-top-color: rgba(255, 255, 255, 0.07);
            color: #94a3b8;
        }

        .dashboard-card .dash-widget-footer .footer-label {
            font-weight: 600;
            color: inherit;
        }

        .dashboard-card .dash-widget-footer .footer-sub {
            font-size: 0.75rem;
            font-weight: 500;
        }
        
        body.dark-mode .card.card-table {
            background-color: rgb(28, 32, 37) !important;
            border-color: rgba(255, 255, 255, 0.08) !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3) !important;
        }
        body.dark-mode .card.card-table .card-header,
        body.dark-mode .card.card-table .card-body,
        body.dark-mode .card.card-table .card-footer {
            background-color: rgb(28, 32, 37) !important;
        }
        body.dark-mode .card.card-chart,
        body.dark-mode .card.card-chart .card-header,
        body.dark-mode .card.card-chart .card-body {
            background-color: rgb(28, 32, 37) !important;
            border-color: rgba(255, 255, 255, 0.08) !important;
        }
        body.dark-mode .card.card-chart .card-header {
            border-bottom: 0 !important;
        }
        body.dark-mode .card.card-table .table-responsive {
            background-color: transparent !important;
            border: 0 !important;
        }
        body.dark-mode .card.card-table .table,
        body.dark-mode .table.dataTable,
        body.dark-mode .dataTables_wrapper table,
        body.dark-mode #sales-table {
            background-color: rgb(28, 32, 37) !important;
            border-collapse: separate !important;
            border-spacing: 0 !important;
            border: 1px solid rgba(255, 255, 255, 0.08) !important;
        }
        body.dark-mode .card.card-table .table td,
        body.dark-mode .card.card-table .table th,
        body.dark-mode .table.dataTable td,
        body.dark-mode .table.dataTable th,
        body.dark-mode #sales-table td,
        body.dark-mode #sales-table th {
            padding: 1rem 0.75rem !important;
            border-top: 1px solid rgba(255, 255, 255, 0.06) !important;
            background-color: transparent !important;
            color: rgb(231, 233, 236) !important;
        }
        body.dark-mode .card.card-table .table tbody tr td:first-child,
        body.dark-mode .card.card-table .table tbody tr th:first-child,
        body.dark-mode .table.dataTable tbody tr td:first-child,
        body.dark-mode .table.dataTable tbody tr th:first-child,
        body.dark-mode #sales-table tbody tr td:first-child,
        body.dark-mode #sales-table tbody tr th:first-child {
            padding-left: 1.5rem !important;
            border-left-width: 0 !important;
        }
        body.dark-mode .card.card-table .table tbody tr td:last-child,
        body.dark-mode .card.card-table .table tbody tr th:last-child,
        body.dark-mode .table.dataTable tbody tr td:last-child,
        body.dark-mode .table.dataTable tbody tr th:last-child,
        body.dark-mode #sales-table tbody tr td:last-child,
        body.dark-mode #sales-table tbody tr th:last-child {
            padding-right: 1.5rem !important;
            border-right-width: 0 !important;
        }
        /* ─────────────────────────────────────────────────────────────────────────────
        ⚖️ MATCH LIGHT-MODE ROW HEIGHT (this is what made dark mode look BIGGER)
        Light mode pads #sales-table tbody cells with 14px 12px
        (style.css: .table.table-hover tbody tr td, .table.table-center tbody tr td).
        Dark mode forces 1rem 0.75rem (16px) through multiple !important overrides,
        so every dark row was 4px taller → the whole table looked slightly bigger.
        This rule re-applies the SAME 14px 12px in dark mode. It intentionally uses a
        selector with an ID (#sales-table) so its specificity wins over every other
        !important override above. Keep the value identical to style.css line 1297.
        ───────────────────────────────────────────────────────────────────────────── */
        body.dark-mode .page-wrapper #sales-table tbody tr td,
        body.dark-mode .page-wrapper #sales-table tbody tr th {
            padding: 14px 12px !important;
        }
        body.dark-mode .card.card-table .table thead th,
        body.dark-mode .card.card-table .table thead td,
        body.dark-mode .card.card-table .table thead tr th,
        body.dark-mode .card.card-table .table thead tr td {
            background-color: rgb(28, 32, 37) !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1) !important;
            color: rgb(201, 203, 207) !important;
        }
        body.dark-mode .card.card-table .table tbody tr {
            border-bottom: 1px solid rgba(255, 255, 255, 0.06) !important;
            background-color: transparent !important;
        }
        body.dark-mode .card.card-table .table tbody tr:hover {
            background-color: rgba(255, 255, 255, 0.03) !important;
        }

        /* Ensure modal content remains readable in dark mode (keep layout/spacing intact) */
        body.dark-mode .modal-content,
        body.dark-mode .modal-footer {
            background-color: rgb(28, 32, 37) !important;
            color: rgb(231, 233, 236) !important;
            border: 1px solid rgba(255, 255, 255, 0.08) !important;
        }

        /* Sell Product modal header styling */
        body.dark-mode .modal-header {
            background-color: rgb(28, 32, 37) !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08) !important;
            color: rgb(231, 233, 236) !important;
        }

        /* Product / Quantity field container styling */
        body.dark-mode .modal .form-group {
            margin-bottom: 1rem;
        }

        body.dark-mode .modal .form-control,
        body.dark-mode .modal .select2-container--default .select2-selection--single,
        body.dark-mode .modal .select2-container--default .select2-selection--multiple {
            background-color: #f8fafc !important;
            color: #111827 !important;
            border: 1px solid rgba(0, 0, 0, 0.18) !important;
            box-shadow: none !important;
        }

        body.dark-mode .modal .select2-container--default .select2-selection--single .select2-selection__rendered,
        body.dark-mode .modal .select2-container--default .select2-selection--single .select2-selection__arrow,
        body.dark-mode .modal .select2-container--default .select2-results__option,
        body.dark-mode .modal .select2-container--default .select2-results__option--highlighted,
        body.dark-mode .modal .select2-container--default .select2-results__option--selected,
        body.dark-mode .modal .modal-title,
        body.dark-mode .modal label,
        body.dark-mode .modal .form-label,
        body.dark-mode .modal .form-control::placeholder {
            color: #000000 !important;
        }

        /* Indicator: change text color on modal title, labels, and placeholders */
        body.dark-mode .modal .modal-title,
        body.dark-mode .modal label,
        body.dark-mode .modal .form-label {
            color: rgba(239, 240, 243, 0.95) !important;
        }

        body.dark-mode .modal .form-control::placeholder {
            color: rgba(3, 3, 3, 0.55) !important;
        }

        /* Product quantity box outlines */
        body.dark-mode .modal .form-control:focus,
        body.dark-mode .modal .select2-container--default .select2-selection--single:focus,
        body.dark-mode .modal .select2-container--default .select2-selection--multiple:focus {
            border-color: rgba(5, 5, 5, 0.32) !important;
            box-shadow: 0 0 0 0.15rem rgba(5, 5, 5, 0.08) !important;
        }

        body.dark-mode .modal .btn-light,
        body.dark-mode .modal .btn {
            color: #050505 !important;
        }
    </style>
@endpush

@push('page-header')
<div class="col-sm-12">
    <div class="dashboard-hero animate__animated animate__fadeInDown">
        <div class="row align-items-center">
            <div class="col-md-8">
                <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill text-xs font-weight-semibold bg-white/20 text-white backdrop-blur-md border border-white/20 mb-2.5 shadow-sm">
                    <span class="hero-live-dot"></span> PHARMACY OPERATIONS
                </div>
                <h3 class="font-weight-bold text-white mb-1.5">Welcome back, {{auth()->user()->name}}!</h3>
                <p class="text-white/90 mb-0">Monitor inventory health, sales activity, and barcode status from your central command panel.</p>
            </div>
            <div class="col-md-4 text-md-right mt-3 mt-md-0">
                <div class="d-inline-flex align-items-center gap-2 px-3 py-2 rounded-xl bg-white/15 backdrop-blur-md border border-white/20 text-white text-xs font-weight-semibold shadow-sm">
                    <i class="fe fe-calendar opacity-80 mr-1"></i>
                    <span id="dashboard-current-datetime">{{ now()->format('D, M j, Y - g:i A') }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endpush

@section('content')
<!-- ┌─────────────────────────────────────────────────────────────────────────────┐ -->
<!-- │ 📊 DASHBOARD INDICATOR CARDS - NO LAYOUT SHIFT                              │ -->
<!-- │ Same layout in Light & Dark Mode - Smooth transition                        │ -->
<!-- └─────────────────────────────────────────────────────────────────────────────┘ -->
<div class="dashboard-indicators-row">
    <!-- 💰 TODAY SALES CASH CARD -->
    <div>
        <div class="card dashboard-card animate__animated animate__fadeInUp dashboard-reveal-1">
            <div class="card-body">
                <div class="dash-widget-header">
                    <span class="dash-widget-icon icon-sales">
                        <i class="fe fe-money"></i>
                    </span>
                    <span class="badge badge-sm font-semibold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200/70 dark:border-emerald-800/70 d-inline-flex align-items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Today
                    </span>
                </div>
                <div class="dash-count mt-2 mb-1">
                    <h3 class="dash-count-val"><span class="currency-sym">{{AppSettings::get('app_currency', '$')}}</span>{{$today_sales}}</h3>
                </div>
                <div class="dash-widget-footer">
                    <span class="footer-label">Today Sales Cash</span>
                    <span class="footer-sub text-emerald-600 dark:text-emerald-400 font-weight-semibold">Live Feed</span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 📁 PRODUCT CATEGORIES CARD -->
    <div>
        <div class="card dashboard-card animate__animated animate__fadeInUp dashboard-reveal-2">
            <div class="card-body">
                <div class="dash-widget-header">
                    <span class="dash-widget-icon icon-categories">
                        <i class="fe fe-credit-card"></i>
                    </span>
                    <span class="badge badge-sm font-semibold bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border border-blue-200/70 dark:border-blue-800/70">
                        Catalog
                    </span>
                </div>
                <div class="dash-count mt-2 mb-1">
                    <h3 class="dash-count-val">{{$total_categories}}</h3>
                </div>
                <div class="dash-widget-footer">
                    <span class="footer-label">Product Categories</span>
                    <span class="footer-sub text-blue-600 dark:text-blue-400 font-weight-semibold">Active</span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- ⚠️ EXPIRED PRODUCTS CARD -->
    <div>
        <div class="card dashboard-card animate__animated animate__fadeInUp dashboard-reveal-3">
            <div class="card-body">
                <div class="dash-widget-header">
                    <span class="dash-widget-icon icon-expired">
                        <i class="fe fe-folder"></i>
                    </span>
                    @if($total_expired_products > 0)
                        <span class="badge badge-sm font-semibold bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200/70 dark:border-rose-800/70">
                            Action Needed
                        </span>
                    @else
                        <span class="badge badge-sm font-semibold bg-slate-100 dark:bg-slate-800/70 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
                            All Good
                        </span>
                    @endif
                </div>
                <div class="dash-count mt-2 mb-1">
                    <h3 class="dash-count-val">{{$total_expired_products}}</h3>
                </div>
                <div class="dash-widget-footer">
                    <span class="footer-label">Expired Products</span>
                    @if($total_expired_products > 0)
                        <span class="footer-sub text-rose-600 dark:text-rose-400 font-weight-semibold">Check Stock</span>
                    @else
                        <span class="footer-sub text-emerald-600 dark:text-emerald-400 font-weight-semibold">Clear</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    <!-- 📦 BARCODE PRODUCTS CARD -->
    <div>
        <div class="card dashboard-card animate__animated animate__fadeInUp dashboard-reveal-4">
            <div class="card-body">
                <div class="dash-widget-header">
                    <span class="dash-widget-icon icon-barcode">
                        <i class="fe fe-barcode"></i>
                    </span>
                    <span class="badge badge-sm font-semibold bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border border-purple-200/70 dark:border-purple-800/70">
                        Barcoded
                    </span>
                </div>
                <div class="dash-count mt-2 mb-1">
                    <h3 class="dash-count-val">{{$total_barcoded_products}}</h3>
                </div>
                <div class="dash-widget-footer">
                    <span class="footer-label">Barcode Products</span>
                    <span class="footer-sub text-purple-600 dark:text-purple-400 font-weight-semibold">Ready</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ┌─────────────────────────────────────────────────────────────────────────────┐ -->
<!-- │ 📋 TODAY SALES & 📊 RESOURCES CONTAINERS - NO LAYOUT SHIFT                 │ -->
<!-- └─────────────────────────────────────────────────────────────────────────────┘ -->
<div class="row dashboard-sales-resources-row">
    <!-- 📋 TODAY SALES TABLE CONTAINER -->
    <div class="col-md-12 col-lg-6">
        <div class="card card-table p-3">
            <div class="dashboard-card-animation dashboard-card-animation--sales animate__animated animate__fadeIn dashboard-reveal-5">
                <div class="card-header border-0 pb-0 d-flex align-items-center justify-content-between mb-3" style="background: transparent;">
                    <div class="d-flex align-items-center">
                        <div style="width: 36px; height: 36px; border-radius: 10px; background: rgba(16, 185, 129, 0.12); color: #10b981; display: flex; align-items: center; justify-content: center; margin-right: 10px; font-size: 1.1rem;">
                            <i class="fe fe-activity font-weight-bold"></i>
                        </div>
                        <div>
                            <h4 class="card-title font-weight-bold mb-0" style="font-size: 1.1rem; letter-spacing: -0.01em;">Today Sales</h4>
                            <span class="text-muted" style="font-size: 0.78rem;">Recent point of sale transactions</span>
                        </div>
                    </div>
                    <span class="badge badge-sm font-semibold bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 border border-emerald-200/60 dark:border-emerald-800/60 d-inline-flex align-items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Live Table
                    </span>
                </div>
                <div class="card-body pt-0">
                    <div class="table-responsive">
                        <div id="sales-table" class="tabulator-table-wrap"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="dashboardProductDetailsModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Product Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="dashboard-product-details-layout">
                        <div class="dashboard-product-details-image"><img class="dashboard-product-detail-image" src="{{ asset('assets/img/productnoimage.png') }}" alt="Product image"></div>
                        <div class="dashboard-product-details-info">
                            <div>
                                <strong>Product</strong>
                                <div class="product-detail-value-wrap">
                                    <span class="dashboard-product-detail product"></span>
                                    <button type="button" class="product-info-btn" aria-label="Product details" data-tooltip="Product: ">
                                        <i class="fas fa-info-circle" aria-hidden="true"></i>
                                    </button>
                                </div>
                            </div>
                            <div><strong>Category</strong><span class="dashboard-product-detail category"></span></div>
                            <div><strong>Supplier</strong><span class="dashboard-product-detail supplier"></span></div>
                            <div><strong>Price</strong><span class="dashboard-product-detail price"></span></div>
                            <div><strong>Quantity</strong><span class="dashboard-product-detail quantity"></span></div>
                            <div><strong>Item Quantity</strong><span class="dashboard-product-detail item_quantity"></span></div>
                            <div><strong>Packaging Box</strong><span class="dashboard-product-detail packaging_box"></span></div>
                            <div><strong>Quantity per Box</strong><span class="dashboard-product-detail quantity_per_box"></span></div>
                            <div><strong>Expire Date</strong><span class="dashboard-product-detail expiry"></span></div>
                            <div><strong>Date of Purchase</strong><span class="dashboard-product-detail purchased"></span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 📊 RESOURCES CHART CONTAINER -->
    <div class="col-md-12 col-lg-6">
        <div class="card card-chart">
            <div class="dashboard-card-animation dashboard-card-animation--resources animate__animated animate__fadeIn dashboard-reveal-6">
                <div class="card-header border-0 pb-0 d-flex align-items-center justify-content-between mb-3" style="background: transparent;">
                    <div class="d-flex align-items-center">
                        <div style="width: 36px; height: 36px; border-radius: 10px; background: rgba(59, 130, 246, 0.12); color: #3b82f6; display: flex; align-items: center; justify-content: center; margin-right: 10px; font-size: 1.1rem;">
                            <i class="fe fe-pie-chart font-weight-bold"></i>
                        </div>
                        <div>
                            <h4 class="card-title font-weight-bold mb-0" style="font-size: 1.1rem; letter-spacing: -0.01em;">Resources</h4>
                            <span class="text-muted" style="font-size: 0.78rem;">Inventory status breakdown</span>
                        </div>
                    </div>
                    <span class="badge badge-sm font-semibold bg-blue-50 dark:bg-blue-950/50 text-blue-700 dark:text-blue-300 border border-blue-200/60 dark:border-blue-800/60">
                        Distribution
                    </span>
                </div>
                <div class="card-body pt-0">
                    <div class="resources-chart-wrapper" aria-live="polite">
                        <div class="resources-chart-processing">
                            <div class="resources-chart-spinner"></div>
                            <span>Processing...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>	
</div>
<!-- ════════════════════════════════════════════════════════════════════════════════ -->
<!-- ✅ NO LAYOUT SHIFT - Same spacing in Light & Dark Mode - Smooth Transition     -->
<!-- ════════════════════════════════════════════════════════════════════════════════ -->

@endsection

@push('page-js')
<script>
    window.pharmacyDashboardInit = async function() {
        const chartWrapper = document.querySelector('.resources-chart-wrapper');
        if (!chartWrapper) {
            return;
        }

        // ─── AUTO-FIT CHART & TABLE TO CONTAINER SIZE ───
        // Re-measure the pie chart and sales table whenever their container
        // width changes (sidebar open/close, window resize, responsive
        // breakpoints), so no stale-width gap is left behind.
        function fitDashboardContent() {
            if (window.pieChart && typeof window.pieChart.resize === 'function') {
                try { window.pieChart.resize(); } catch (e) {}
            }
            if (window.PharmaTabulator) {
                const salesTable = window.PharmaTabulator.get('sales-table');
                if (salesTable && typeof salesTable.redraw === 'function') {
                    try { salesTable.redraw(); } catch (e) {}
                }
            }
        }

        document.addEventListener('pharmacy:theme-changed', function (event) {
            if (!window.pieChart || !window.pieChart.options || !window.pieChart.options.legend) {
                return;
            }
            window.pieChart.options.legend.labels.fontColor = event.detail && event.detail.isDark ? '#f8fafc' : '#111827';
            window.pieChart.update();
        });

        if (window.dashboardFitObserver) {
            window.dashboardFitObserver.disconnect();
            window.dashboardFitObserver = null;
        }
        if (window.ResizeObserver) {
            window.dashboardFitObserver = new ResizeObserver(function () {
                if (window.dashboardFitRaf) {
                    window.cancelAnimationFrame(window.dashboardFitRaf);
                }
                window.dashboardFitRaf = window.requestAnimationFrame(fitDashboardContent);
            });
            const salesCard = document.querySelector('.dashboard-card-animation--sales');
            const resourcesCard = document.querySelector('.dashboard-card-animation--resources');
            if (salesCard) {
                window.dashboardFitObserver.observe(salesCard);
            }
            if (resourcesCard) {
                window.dashboardFitObserver.observe(resourcesCard);
            }
        }

        const requestId = (window.dashboardChartRequestId || 0) + 1;
        window.dashboardChartRequestId = requestId;
        chartWrapper.innerHTML = '<div class="resources-chart-processing"><div class="resources-chart-spinner"></div><span>Processing...</span></div>';

        // ═══ INITIALIZE SALES TABLE ═══
        if (document.getElementById('sales-table') && window.PharmaTabulator) {
            window.PharmaTabulator.server({
                el: 'sales-table',
                url: "{{route('sales.index')}}",
                pageLength: 5,
                paginationSizeSelector: false,
                columns: [
                    {title: 'Product', field: 'product', formatter: 'html'},
                    {title: 'Quantity', field: 'quantity'},
                    {title: 'Total Price', field: 'total_price'},
                    {title: 'Date', field: 'date'},
                ]
            });

            window.requestAnimationFrame(function () {
                fitDashboardContent();
                window.setTimeout(fitDashboardContent, 250);
            });

            $(document).off('click.dashboardProductDetails').on('click.dashboardProductDetails', '.sale-product-detail-link', function (event) {
                event.preventDefault();
                let details = {};
                try {
                    details = JSON.parse($(this).attr('data-details') || '{}');
                } catch (error) {
                    return;
                }
                const productName = details.product || '';
                const productTooltip = productName ? 'Product:\n' + productName : 'No product name';
                $('#dashboardProductDetailsModal .product-info-btn')
                    .attr('data-tooltip', productTooltip)
                    .attr('aria-label', productTooltip);
                $('#dashboardProductDetailsModal .dashboard-product-detail.product').attr('title', productName);

                Object.keys(details).forEach(function (key) {
                    if (key === 'image') {
                        $('#dashboardProductDetailsModal .dashboard-product-detail-image').attr('src', details[key] || '{{ asset('assets/img/productnoimage.png') }}');
                        return;
                    }
                    $('#dashboardProductDetailsModal .dashboard-product-detail.' + key).text(details[key] || '');
                });
                $('#dashboardProductDetailsModal').modal('show');
            });
        }

        try {
            const response = await fetch('{{ route('dashboard.resources') }}', {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                cache: 'no-store'
            });
            if (!response.ok) throw new Error('Resource request failed: ' + response.status);

            const counts = await response.json();
            if (requestId !== window.dashboardChartRequestId || !chartWrapper.isConnected) {
                return;
            }
            const pieData = [
                Number(counts.purchases) || 0,
                Number(counts.suppliers) || 0,
                Number(counts.sales) || 0
            ];
            const total = pieData.reduce((sum, value) => sum + value, 0);
            window.dashboardChartData = {
                labels: ['Total Purchases', 'Total Suppliers', 'Total Sales'],
                purchases: pieData[0],
                suppliers: pieData[1],
                sales: pieData[2],
                normalized: pieData,
                rawTotal: total,
                fallback: total === 0
            };

            if (window.pieChart && typeof window.pieChart.destroy === 'function') {
                window.pieChart.destroy();
            }
            window.pieChart = null;
            chartWrapper.innerHTML = '';

            if (total === 0) {
                const emptyHolder = document.createElement('div');
                emptyHolder.className = 'resources-chart-empty';
                emptyHolder.textContent = 'No resource data available';
                chartWrapper.appendChild(emptyHolder);
                return;
            }

            const chartHolder = document.createElement('div');
            chartHolder.className = 'resources-chart-holder';
            const newCanvas = document.createElement('canvas');
            newCanvas.id = 'pieChart';
            chartHolder.appendChild(newCanvas);
            chartWrapper.appendChild(chartHolder);
            window.pieChart = new Chart(newCanvas, {
                        type: 'pie',
                        data: {
                            labels: window.dashboardChartData.labels,
                            datasets: [{
                                backgroundColor: ['#FF6384', '#36A2EB', '#7bb13c'],
                                hoverBackgroundColor: ['#FF6384', '#36A2EB', '#7bb13c'],
                                data: pieData
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            animation: {
                                duration: 450,
                                easing: 'easeOutQuart',
                                animateRotate: true,
                                animateScale: true
                            },
                            legend: {
                                position: 'bottom',
                                align: 'center',
                                labels: {
                                    fontSize: 12,
                                    padding: 30,
                                    fontColor: document.body.classList.contains('dark-mode') ? '#f8fafc' : '#333333'
                                }
                            },
                            layout: {
                                padding: {
                                    top: 8,
                                    bottom: 8,
                                    left: 0,
                                    right: 0
                                }
                            }
                        }
                    });
        } catch (error) {
            if (requestId !== window.dashboardChartRequestId || !chartWrapper.isConnected) {
                return;
            }
            console.error('Dashboard resources failed to load:', error);
            chartWrapper.innerHTML = '';
            const errorHolder = document.createElement('div');
            errorHolder.className = 'resources-chart-empty';
            errorHolder.textContent = 'Unable to load resource data';
            chartWrapper.appendChild(errorHolder);
        } finally {
            if (requestId === window.dashboardChartRequestId) {
                window.dashboardChartLoading = false;
            }
        }
    };

</script>
@endpush