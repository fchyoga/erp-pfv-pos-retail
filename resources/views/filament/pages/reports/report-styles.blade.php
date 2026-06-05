<style>
    /* Report Stats Overview Cards Custom Grid & Card layout */
    .report-stats-grid {
        display: grid !important;
        grid-template-columns: 1fr !important;
        gap: 1.5rem !important;
        margin-bottom: 1.5rem !important;
    }
    
    @media (min-width: 768px) {
        .report-stats-grid.cols-3 {
            grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
        }
        .report-stats-grid.cols-4 {
            grid-template-columns: repeat(4, minmax(0, 1fr)) !important;
        }
    }
    
    .report-stat-card {
        background-color: #ffffff !important;
        padding: 1.5rem !important;
        border-radius: 16px !important;
        border: 1px solid #eef2f6 !important;
        box-shadow: 0 4px 12px -2px rgba(0, 0, 0, 0.02) !important;
        display: flex !important;
        flex-direction: row !important;
        align-items: center !important;
        gap: 1rem !important;
        transition: transform 0.2s ease, box-shadow 0.2s ease !important;
    }
    
    .report-stat-card:hover {
        transform: translateY(-2px) !important;
        box-shadow: 0 8px 16px -2px rgba(22, 163, 74, 0.08) !important;
    }
    
    .report-stat-icon-wrapper {
        padding: 0.75rem !important;
        border-radius: 12px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        flex-shrink: 0 !important;
    }
    
    /* Colors for icon wrappers */
    .report-stat-icon-wrapper.bg-emerald {
        background-color: #e8f7ec !important;
        color: #16a34a !important;
    }
    
    .report-stat-icon-wrapper.bg-blue {
        background-color: #eff6ff !important;
        color: #3b82f6 !important;
    }
    
    .report-stat-icon-wrapper.bg-violet {
        background-color: #f5f3ff !important;
        color: #8b5cf6 !important;
    }
    
    .report-stat-icon-wrapper.bg-amber {
        background-color: #fffbeb !important;
        color: #d97706 !important;
    }
    
    .report-stat-icon-wrapper.bg-orange {
        background-color: #fff7ed !important;
        color: #ea580c !important;
    }
    
    .report-stat-icon-wrapper.bg-red {
        background-color: #fef2f2 !important;
        color: #dc2626 !important;
    }
    
    .report-stat-icon-wrapper.bg-gray {
        background-color: #f3f4f6 !important;
        color: #4b5563 !important;
    }
    
    /* Card typography */
    .report-stat-label {
        font-size: 0.75rem !important;
        font-weight: 600 !important;
        color: #94a3b8 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.05em !important;
        margin: 0 !important;
        line-height: 1.25 !important;
    }
    
    .report-stat-value {
        font-size: 1.5rem !important;
        font-weight: 800 !important;
        color: #1e293b !important;
        margin-top: 0.25rem !important;
        margin-bottom: 0 !important;
        line-height: 1.25 !important;
    }
</style>
