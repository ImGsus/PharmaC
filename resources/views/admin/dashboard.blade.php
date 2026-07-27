@extends('admin.layouts.app')

<x-assets.datatables />

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
        /* │ 🔧 CUSTOMIZATION VARIABLES - Light Mode                                   │ */
        /* └─────────────────────────────────────────────────────────────────────────────┘ */
        :root {
            /* ═══ DASHBOARD INDICATOR CARDS STYLING ═══ */
            /* 📏 BOX SIZE */
            --dashboard-card-width: 100%;
            /* 📐 RESIZE HEIGHT - REDUCE OR INCREASE THIS */
            --dashboard-card-min-height: auto;                 /* auto=default, 100px=small, 120px=medium, 150px=large */
            --dashboard-card-max-height: none;                 /* none=no limit, 200px, 250px, 300px */
            
            /* 🔲 BOX SHAPE - CHANGE TO RESHAPE */
            --dashboard-card-border-radius: 20px;             /* 0px=square, 10px=slight, 20px=rounded, 30px=very rounded */
            /* 🔴 BORDER THICKNESS - REDUCE OR INCREASE THIS */
            --dashboard-card-border-width: 1px;                  /* 0px=none, 0.5px=super thin, 1px=thin, 2px=thick, 3px=very thick */
            
            /* 📍 SPACING - SAME FOR BOTH MODES TO PREVENT SHIFT */
            --dashboard-card-padding: 1.25rem;
            --dashboard-card-margin: 0;
            --dashboard-card-gap: 0.75rem;
            
            /* 🎯 MOVE BOXES - CHANGE THESE TO MOVE CARDS */
            --dashboard-card-move-x: 0;                         /* Move Left/Right: -20px=left, 0=center, 20px=right */
            --dashboard-card-move-y: 0;                         /* Move Up/Down: -10px=up, 0=normal, 10px=down */
            
            /* 🎨 COLORS - LIGHT MODE */
            --dashboard-card-bg: #ffffff;
            --dashboard-card-border-color: #ffffff;              /* White border - change to adjust border color */
            --dashboard-card-border-outline: 1px solid rgba(71, 85, 105, 0.2);  /* Subtle outline for visibility */
            --dashboard-card-border-hover: rgba(51, 65, 85, 0.75);
            --dashboard-card-shadow: 0 0 0 1px rgba(148, 163, 184, 0.24), 0 12px 28px rgba(0, 0, 0, 0.08);
            --dashboard-card-shadow-hover: 0 0 0 1px rgba(148, 163, 184, 0.30), 0 14px 32px rgba(0, 0, 0, 0.12);
            
            /* ═══ TODAY SALES TABLE STYLING ═══ */
            /* 📏 BOX SIZE */
            --sales-table-width: 100%;
            /* 📐 RESIZE HEIGHT - REDUCE OR INCREASE THIS */
            --sales-table-min-height: auto;                   /* auto=default, 200px=short, 300px=medium, 400px=tall */
            --sales-table-max-height: none;                   /* none=no limit, 300px, 400px, 500px */
            
            /* 🔲 BOX SHAPE - CHANGE TO RESHAPE */
            --sales-table-border-radius: 10px;               /* 0px=square, 10px=slight, 15px=rounded, 20px=very rounded */
            /* 🔴 BORDER THICKNESS - REDUCE OR INCREASE THIS */
            --sales-table-border-width: 1px;                    /* 0px=none, 0.5px=super thin, 1px=thin, 2px=thick, 3px=very thick */
            
            /* 📍 SPACING - SAME FOR BOTH MODES */
            --sales-table-padding: 1.25rem;
            --sales-table-margin: 0 0 1.25rem 0;
            
            /* 🎯 MOVE BOX - CHANGE THESE TO MOVE TABLE */
            --sales-table-move-x: 0;                            /* Move Left/Right: -20px=left, 0=center, 20px=right */
            --sales-table-move-y: -10px;                         /* Move Up/Down: -10px=up, 0=normal, 10px=down */
            
            /* 🎨 COLORS - LIGHT MODE */
            --sales-table-bg: #ffffff;
            --sales-table-border-color: #ffffff;                /* White border - change to adjust border color */
            --sales-table-border-outline: 1px solid rgba(71, 85, 105, 0.2);  /* Subtle outline for visibility */
            --sales-table-border-hover: rgba(51, 65, 85, 0.75);
            --sales-table-shadow: 0 0 0 1px rgba(148, 163, 184, 0.24), 0 12px 28px rgba(0, 0, 0, 0.08);
            --sales-table-shadow-hover: 0 0 0 1px rgba(148, 163, 184, 0.30), 0 14px 32px rgba(0, 0, 0, 0.12);
            
            /* ═══ RESOURCES CHART STYLING ═══ */
            /* 📏 BOX SIZE */
            --resources-width: 100%;
            /* 📐 RESIZE HEIGHT - REDUCE OR INCREASE THIS */
            --resources-min-height: 392px;                      /* auto=default, 250px=short, 300px=medium, 350px=tall */
            --resources-max-height: 400px;                      /* none=no limit, 300px, 350px, 400px */
            /* 📊 PIE CHART SIZE INSIDE BOX */
            --resources-chart-height: 260px;                  /* 220px=smaller, 260px=medium, 300px=larger */
            --resources-chart-max-width: 100%;
            
            /* 🔲 BOX SHAPE - CHANGE TO RESHAPE */
            --resources-border-radius: 10px;                 /* 0px=square, 10px=slight, 15px=rounded, 20px=very rounded */
            /* 🔴 BORDER THICKNESS - REDUCE OR INCREASE THIS */
            --resources-border-width: 1px;                      /* 0px=none, 0.5px=super thin, 1px=thin, 2px=thick, 3px=very thick */
            
            /* 📍 SPACING - SAME FOR BOTH MODES */
            --resources-padding: 1.25rem;
            --resources-margin: 0;
            
            /* 🎯 MOVE BOX - CHANGE THESE TO MOVE CHART */
            --resources-move-x: 0;                              /* Move Left/Right: -20px=left, 0=center, 20px=right */
            --resources-move-y: -10px;                              /* Move Up/Down: -10px=up, 0=normal, 10px=down */
            
            /* 🎨 COLORS - LIGHT MODE */
            --resources-bg: #ffffff;
            --resources-border-color: #ffffff;                  /* White border - change to adjust border color */
            --resources-border-outline: 1px solid rgba(71, 85, 105, 0.2);  /* Subtle outline for visibility */
            --resources-border-hover: rgba(51, 65, 85, 0.75);
            --resources-shadow: 0 0 0 1px rgba(148, 163, 184, 0.24), 0 12px 28px rgba(0, 0, 0, 0.08);
            --resources-shadow-hover: 0 0 0 1px rgba(148, 163, 184, 0.30), 0 14px 32px rgba(0, 0, 0, 0.12);
        }

        /* ┌─────────────────────────────────────────────────────────────────────────────┐ */
        /* │ 🌙 CUSTOMIZATION VARIABLES - Dark Mode (Same layout, different colors)    │ */
        /* │   Note: All position & thickness variables are SAME as Light Mode         │ */
        /* │   Only colors change - NO LAYOUT SHIFT!                                   │ */
        /* └─────────────────────────────────────────────────────────────────────────────┘ */
        body.dark-mode {
            /* ═══ DASHBOARD CARDS - Same spacing & positioning, only colors change ═══ */
            /* 🎯 MOVE & THICKNESS SAME AS LIGHT MODE - See :root variables above */
            --dashboard-card-bg: #111827;
            --dashboard-card-border-color: rgba(255, 255, 255, 0.08);         /* Very subtle border - same look as Light Mode */
            --dashboard-card-border-outline: 1px solid rgba(255, 255, 255, 0.08);  /* Same subtle outline for consistency */
            --dashboard-card-border-hover: rgba(255, 255, 255, 0.35);
            --dashboard-card-shadow: 0 0 0 1px rgba(255, 255, 255, 0.08), 0 12px 28px rgba(0, 0, 0, 0.45);
            --dashboard-card-shadow-hover: 0 0 0 1px rgba(255, 255, 255, 0.12), 0 14px 32px rgba(0, 0, 0, 0.55);
            
            /* TODAY SALES - Same spacing, only colors change */
            --sales-table-bg: #111827;
            --sales-table-border-color: rgba(255, 255, 255, 0.08);            /* Very subtle border - same look as Light Mode */
            --sales-table-border-outline: 1px solid rgba(255, 255, 255, 0.08);  /* Same subtle outline for consistency */
            /* 🎯 MOVE TODAY SALES BOX DOWN IN NIGHT MODE */
            --sales-table-move-x: 0;                            /* Keep at 0 for horizontal */
            --sales-table-move-y: 15px;                         /* Move down: change to 0 for normal, 20px for more down */
            --sales-table-border-hover: rgba(255, 255, 255, 0.35);
            --sales-table-shadow: 0 0 0 1px rgba(255, 255, 255, 0.08), 0 12px 28px rgba(0, 0, 0, 0.45);
            --sales-table-shadow-hover: 0 0 0 1px rgba(255, 255, 255, 0.12), 0 14px 32px rgba(0, 0, 0, 0.55);
            
            /* RESOURCES - Same spacing, only colors change */
            --resources-bg: #111827;
            --resources-border-color: rgba(255, 255, 255, 0.08);              /* Very subtle border - same look as Light Mode */
            --resources-border-outline: 1px solid rgba(255, 255, 255, 0.08);  /* Same subtle outline for consistency */
            --resources-min-height: 400px;                      /* auto=default, 250px=short, 300px=medium, 350px=tall */
            --resources-max-height: 400px;                     /* none=no limit, 300px, 350px, 400px */
            /* 🎯 MOVE RESOURCES BOX DOWN IN NIGHT MODE */
            --resources-move-x: 0;                              /* Keep at 0 for horizontal */
            --resources-move-y: 15px;                           /* Move down: change to 0 for normal, 20px for more down */
            --resources-border-hover: rgba(255, 255, 255, 0.35);
            --resources-shadow: 0 0 0 1px rgba(255, 255, 255, 0.08), 0 12px 28px rgba(0, 0, 0, 0.45);
            --resources-shadow-hover: 0 0 0 1px rgba(255, 255, 255, 0.12), 0 14px 32px rgba(0, 0, 0, 0.55);
        }

        /* ┌─────────────────────────────────────────────────────────────────────────────┐ */
        /* │ 🎨 LIGHT MODE - Dashboard Indicator Cards                                 │ */
        /* └─────────────────────────────────────────────────────────────────────────────┘ */
        .dashboard-indicators-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: var(--dashboard-card-gap);
            margin-bottom: var(--dashboard-card-gap);
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
            
            /* 🎯 MOVE BOX - Transform to position */
            transform: translateX(var(--sales-table-move-x)) translateY(var(--sales-table-move-y));
            
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
            
            /* 🎯 MOVE BOX - Transform to position */
            transform: translateX(var(--resources-move-x)) translateY(var(--resources-move-y));
            
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
            width: 100%;
            min-height: var(--resources-chart-height);
        }

        .resources-chart-wrapper canvas,
        .resources-chart-wrapper .chart-container {
            max-width: var(--resources-chart-max-width);
            width: 100%;
            height: var(--resources-chart-height) !important;
            margin: 0 auto;
        }

        .card-chart:hover,
        .card-chart:focus-within {
            border-color: var(--resources-border-hover);
            box-shadow: var(--resources-shadow-hover);
        }

        /* ╔═════════════════════════════════════════════════════════════════════════════╗ */
        /* ║  Other Dashboard Elements                                                  ║ */
        /* ╚═════════════════════════════════════════════════════════════════════════════╝ */

        .dashboard-hero {
            background-image: linear-gradient(180deg, rgba(16,78,146,0.85), rgba(44, 62, 80, 0.9)), url('{{asset('assets/img/img-01.jpg')}}');
            background-size: cover;
            background-position: center;
            border-radius: 18px;
            color: #fff;
            padding: 30px;
            margin-bottom: 25px;
            box-shadow: 0 16px 40px rgba(0,0,0,0.12);
        }
        .dashboard-hero h3 {
            font-size: 2.2rem;
            margin-bottom: 0.5rem;
        }
        .dashboard-hero p {
            color: rgba(255,255,255,0.8);
            margin-bottom: 0;
        }

        .dashboard-card .dash-widget-icon {
            width: 56px;
            height: 56px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 16px;
            font-size: 1.25rem;
        }

        .dashboard-card .dash-widget-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
        }

        .dashboard-card .dash-widget-header .dash-count h3 {
            font-weight: 700;
            font-size: 1.75rem;
            margin: 0;
            line-height: 1.2;
        }

        body.dark-mode .dashboard-card .dash-widget-header .dash-count h3 {
            color: #F8FAFC;
        }

        .dashboard-card .dash-widget-info h6 {
            font-size: 0.875rem;
            font-weight: 500;
            margin-bottom: 0.75rem;
            text-transform: capitalize;
        }

        body.dark-mode .dashboard-card .dash-widget-info h6 {
            color: #9CA3AF;
        }

        .dashboard-card .progress {
            height: 6px;
            border-radius: 3px;
            overflow: hidden;
        }

        body.dark-mode .dashboard-card .progress {
            background-color: rgba(255, 255, 255, 0.12);
        }

        /* Color utilities */
        .dashboard-card .bg-soft-primary {
            background-color: rgba(59, 130, 246, 0.1) !important;
            color: #3B82F6 !important;
        }

        .dashboard-card .bg-soft-success {
            background-color: rgba(34, 197, 94, 0.1) !important;
            color: #22C55E !important;
        }

        .dashboard-card .bg-soft-danger {
            background-color: rgba(239, 68, 68, 0.1) !important;
            color: #EF4444 !important;
        }

        .dashboard-card .bg-soft-info {
            background-color: rgba(14, 165, 233, 0.1) !important;
            color: #0EA5E9 !important;
        }

        body.dark-mode .dashboard-card .bg-soft-primary {
            background-color: rgba(59, 130, 246, 0.2) !important;
            color: #60A5FA !important;
        }

        body.dark-mode .dashboard-card .bg-soft-success {
            background-color: rgba(34, 197, 94, 0.2) !important;
            color: #4ADE80 !important;
        }

        body.dark-mode .dashboard-card .bg-soft-danger {
            background-color: rgba(239, 68, 68, 0.2) !important;
            color: #F87171 !important;
        }

        body.dark-mode .dashboard-card .bg-soft-info {
            background-color: rgba(14, 165, 233, 0.2) !important;
            color: #38BDF8 !important;
        }

        /* Progress bar colors */
        .dashboard-card .bg-primary { background-color: #3B82F6 !important; }
        .dashboard-card .bg-success { background-color: #22C55E !important; }
        .dashboard-card .bg-danger { background-color: #EF4444 !important; }
        .dashboard-card .bg-info { background-color: #0EA5E9 !important; }
        body.dark-mode .card.card-table {
            background-color: #111827 !important;
            border-color: rgba(255, 255, 255, 0.10) !important;
            box-shadow: 0 0 0 1px rgba(255, 255, 255, 0.05) !important;
        }
        body.dark-mode .card.card-table .card-header,
        body.dark-mode .card.card-table .card-body,
        body.dark-mode .card.card-table .card-footer {
            background-color: #111827 !important;
        }
        body.dark-mode .card.card-chart,
        body.dark-mode .card.card-chart .card-header,
        body.dark-mode .card.card-chart .card-body {
            background-color: #111827 !important;
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
            background-color: #111827 !important;
            border-collapse: separate !important;
            border-spacing: 0 !important;
            border: 2px solid rgba(255, 255, 255, 0.14) !important;
        }
        body.dark-mode .card.card-table .table td,
        body.dark-mode .card.card-table .table th,
        body.dark-mode .table.dataTable td,
        body.dark-mode .table.dataTable th,
        body.dark-mode #sales-table td,
        body.dark-mode #sales-table th {
            padding: 1rem 0.75rem !important;
            border-top: 1px solid rgba(255, 255, 255, 0.12) !important;
            background-color: transparent !important;
            color: #f8fafc !important;
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
        body.dark-mode .card.card-table .table thead th,
        body.dark-mode .card.card-table .table thead td,
        body.dark-mode .card.card-table .table thead tr th,
        body.dark-mode .card.card-table .table thead tr td {
            background-color: #111827 !important;
            border-bottom: 2px solid rgba(255, 255, 255, 0.16) !important;
        }
        body.dark-mode .card.card-table .table tbody tr {
            border-bottom: 2px solid rgba(255, 255, 255, 0.08) !important;
            background-color: transparent !important;
        }
        body.dark-mode .card.card-table .table tbody tr:hover {
            background-color: rgba(255, 255, 255, 0.08) !important;
        }

        /* Ensure modal content remains readable in dark mode (keep layout/spacing intact) */
        body.dark-mode .modal-content,
        body.dark-mode .modal-footer {
            background-color: #070707 !important;
            color: #000000 !important;
            border: 1px solid rgba(0, 0, 0, 0.16) !important;
        }

        /* Sell Product modal header styling */
        body.dark-mode .modal-header {
            background-color: #000000 !important;
            border-bottom: 1px solid rgba(0, 0, 0, 0.12) !important;
            color: #050505 !important;
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
    <div class="dashboard-hero">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h3>Welcome back, {{auth()->user()->name}}!</h3>
                <p>Monitor inventory health, expired products, and barcode-ready stock from a clean control panel.</p>
            </div>
            <div class="col-md-4 text-md-end mt-4 mt-md-0">
                <a href="{{ route('products.scan', ['origin' => 'sales_add']) }}" class="btn btn-light btn-lg">Scan Barcode</a>
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
        <div class="card dashboard-card">
            <div class="card-body">
                <div class="dash-widget-header">
                    <span class="dash-widget-icon text-primary bg-soft-primary">
                        <i class="fe fe-money"></i>
                    </span>
                    <div class="dash-count">
                        <h3>{{AppSettings::get('app_currency', '$')}} {{$today_sales}}</h3>
                    </div>
                </div>
                <div class="dash-widget-info">
                    <h6 class="text-muted">Today Sales Cash</h6>
                    <div class="progress progress-sm">
                        <div class="progress-bar bg-primary w-50"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 📁 PRODUCT CATEGORIES CARD -->
    <div>
        <div class="card dashboard-card">
            <div class="card-body">
                <div class="dash-widget-header">
                    <span class="dash-widget-icon text-success bg-soft-success">
                        <i class="fe fe-credit-card"></i>
                    </span>
                    <div class="dash-count">
                        <h3>{{$total_categories}}</h3>
                    </div>
                </div>
                <div class="dash-widget-info">
                    <h6 class="text-muted">Product Categories</h6>
                    <div class="progress progress-sm">
                        <div class="progress-bar bg-success w-50"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- ⚠️ EXPIRED PRODUCTS CARD -->
    <div>
        <div class="card dashboard-card">
            <div class="card-body">
                <div class="dash-widget-header">
                    <span class="dash-widget-icon text-danger bg-soft-danger">
                        <i class="fe fe-folder"></i>
                    </span>
                    <div class="dash-count">
                        <h3>{{$total_expired_products}}</h3>
                    </div>
                </div>
                <div class="dash-widget-info">
                    <h6 class="text-muted">Expired Products</h6>
                    <div class="progress progress-sm">
                        <div class="progress-bar bg-danger w-50"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 📦 BARCODE PRODUCTS CARD -->
    <div>
        <div class="card dashboard-card">
            <div class="card-body">
                <div class="dash-widget-header">
                    <span class="dash-widget-icon text-info bg-soft-info">
                        <i class="fe fe-barcode"></i>
                    </span>
                    <div class="dash-count">
                        <h3>{{$total_barcoded_products}}</h3>
                    </div>
                </div>
                <div class="dash-widget-info">
                    <h6 class="text-muted">Barcode Products</h6>
                    <div class="progress progress-sm">
                        <div class="progress-bar bg-info w-50"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ┌─────────────────────────────────────────────────────────────────────────────┐ -->
<!-- │ 📋 TODAY SALES & 📊 RESOURCES CONTAINERS - NO LAYOUT SHIFT                 │ -->
<!-- └─────────────────────────────────────────────────────────────────────────────┘ -->
<div class="row">
    <!-- 📋 TODAY SALES TABLE CONTAINER -->
    <div class="col-md-12 col-lg-6">
        <div class="card card-table p-3">
            <div class="card-header">
                <h4 class="card-title ">Today Sales</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="sales-table" class="datatable table table-hover table-center mb-0">
                        <thead>
                            <tr>
                                <th>Medicine</th>
                                <th>Quantity</th>
                                <th>Total Price</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                                                                                      
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- 📊 RESOURCES CHART CONTAINER -->
    <div class="col-md-12 col-lg-6">
        <div class="card card-chart">
            <div class="card-header">
                <h4 class="card-title text-center">Resources</h4>
            </div>
            <div class="card-body">
                <div class="resources-chart-wrapper">
                    {!! $pieChart->render() !!}
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
    $(document).ready(function() {
        $('#sales-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{route('sales.index')}}",
            columns: [
                {data: 'product', name: 'product'},
                {data: 'quantity', name: 'quantity'},
                {data: 'total_price', name: 'total_price'},
                {data: 'date', name: 'date'},
            ]
        });
    });
</script> 
<script src="{{asset('assets/plugins/chart.js/Chart.bundle.min.js')}}"></script>
@endpush