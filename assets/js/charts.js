/**
 * GateWey Requisition Management System
 * Chart Configuration & Utilities
 * 
 * File: assets/js/charts.js
 * Purpose: Chart.js configurations and helper functions for dashboard charts
 * 
 * Requires: Chart.js 4.x (loaded via CDN in dashboard pages)
 */

/**
 * Default Chart.js configuration
 */
const defaultChartConfig = {
    responsive: true,
    maintainAspectRatio: true,
    plugins: {
        legend: {
            position: 'top',
            labels: {
                font: {
                    family: "'Inter', sans-serif",
                    size: 12
                },
                padding: 15,
                usePointStyle: true
            }
        },
        tooltip: {
            backgroundColor: 'rgba(0, 0, 0, 0.8)',
            padding: 12,
            titleFont: {
                size: 14,
                weight: 'bold'
            },
            bodyFont: {
                size: 13
            },
            borderColor: 'rgba(255, 255, 255, 0.1)',
            borderWidth: 1,
            displayColors: true,
            callbacks: {
                label: function(context) {
                    let label = context.dataset.label || '';
                    if (label) {
                        label += ': ';
                    }
                    // Check if value is currency
                    if (context.dataset.isCurrency) {
                        label += formatCurrency(context.parsed.y);
                    } else {
                        label += context.parsed.y.toLocaleString();
                    }
                    return label;
                }
            }
        }
    },
    scales: {
        y: {
            beginAtZero: true,
            ticks: {
                font: {
                    size: 11
                },
                padding: 8
            },
            grid: {
                color: 'rgba(0, 0, 0, 0.05)',
                drawBorder: false
            }
        },
        x: {
            ticks: {
                font: {
                    size: 11
                },
                padding: 8
            },
            grid: {
                display: false,
                drawBorder: false
            }
        }
    }
};

/**
 * Color schemes for charts
 */
const chartColors = {
    primary: {
        background: 'rgba(99, 102, 241, 0.2)',
        border: 'rgba(99, 102, 241, 1)',
        solid: '#6366f1'
    },
    success: {
        background: 'rgba(16, 185, 129, 0.2)',
        border: 'rgba(16, 185, 129, 1)',
        solid: '#10b981'
    },
    warning: {
        background: 'rgba(245, 158, 11, 0.2)',
        border: 'rgba(245, 158, 11, 1)',
        solid: '#f59e0b'
    },
    danger: {
        background: 'rgba(239, 68, 68, 0.2)',
        border: 'rgba(239, 68, 68, 1)',
        solid: '#ef4444'
    },
    info: {
        background: 'rgba(59, 130, 246, 0.2)',
        border: 'rgba(59, 130, 246, 1)',
        solid: '#3b82f6'
    },
    secondary: {
        background: 'rgba(107, 114, 128, 0.2)',
        border: 'rgba(107, 114, 128, 1)',
        solid: '#6b7280'
    }
};

/**
 * Format currency for Nigerian Naira
 * 
 * @param {number} value - Amount to format
 * @returns {string} Formatted currency string
 */
function formatCurrency(value) {
    return '₦ ' + value.toLocaleString('en-NG', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

/**
 * Format number with thousands separator
 * 
 * @param {number} value - Number to format
 * @returns {string} Formatted number string
 */
function formatNumber(value) {
    return value.toLocaleString('en-NG');
}

/**
 * Create a line chart
 * 
 * @param {string} canvasId - Canvas element ID
 * @param {object} data - Chart data
 * @param {object} options - Chart options (optional)
 * @returns {Chart} Chart.js instance
 */
function createLineChart(canvasId, data, options = {}) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) {
        console.error('Canvas element not found:', canvasId);
        return null;
    }

    const config = {
        type: 'line',
        data: data,
        options: {
            ...defaultChartConfig,
            ...options,
            plugins: {
                ...defaultChartConfig.plugins,
                ...(options.plugins || {})
            }
        }
    };

    return new Chart(ctx, config);
}

/**
 * Create a bar chart
 * 
 * @param {string} canvasId - Canvas element ID
 * @param {object} data - Chart data
 * @param {object} options - Chart options (optional)
 * @returns {Chart} Chart.js instance
 */
function createBarChart(canvasId, data, options = {}) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) {
        console.error('Canvas element not found:', canvasId);
        return null;
    }

    const config = {
        type: 'bar',
        data: data,
        options: {
            ...defaultChartConfig,
            ...options,
            plugins: {
                ...defaultChartConfig.plugins,
                ...(options.plugins || {})
            }
        }
    };

    return new Chart(ctx, config);
}

/**
 * Create a pie chart
 * 
 * @param {string} canvasId - Canvas element ID
 * @param {object} data - Chart data
 * @param {object} options - Chart options (optional)
 * @returns {Chart} Chart.js instance
 */
function createPieChart(canvasId, data, options = {}) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) {
        console.error('Canvas element not found:', canvasId);
        return null;
    }

    const config = {
        type: 'pie',
        data: data,
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'right',
                    labels: {
                        font: {
                            family: "'Inter', sans-serif",
                            size: 12
                        },
                        padding: 15,
                        usePointStyle: true
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
                    titleFont: {
                        size: 14,
                        weight: 'bold'
                    },
                    bodyFont: {
                        size: 13
                    }
                }
            },
            ...options
        }
    };

    return new Chart(ctx, config);
}

/**
 * Create a doughnut chart
 * 
 * @param {string} canvasId - Canvas element ID
 * @param {object} data - Chart data
 * @param {object} options - Chart options (optional)
 * @returns {Chart} Chart.js instance
 */
function createDoughnutChart(canvasId, data, options = {}) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) {
        console.error('Canvas element not found:', canvasId);
        return null;
    }

    const config = {
        type: 'doughnut',
        data: data,
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'right',
                    labels: {
                        font: {
                            family: "'Inter', sans-serif",
                            size: 12
                        },
                        padding: 15,
                        usePointStyle: true
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
                    titleFont: {
                        size: 14,
                        weight: 'bold'
                    },
                    bodyFont: {
                        size: 13
                    }
                }
            },
            ...options
        }
    };

    return new Chart(ctx, config);
}

/**
 * Generate color palette for multiple datasets
 * 
 * @param {number} count - Number of colors needed
 * @returns {array} Array of color objects with background and border colors
 */
function generateColorPalette(count) {
    const baseColors = [
        chartColors.primary,
        chartColors.success,
        chartColors.warning,
        chartColors.danger,
        chartColors.info,
        chartColors.secondary
    ];

    const colors = [];
    for (let i = 0; i < count; i++) {
        colors.push(baseColors[i % baseColors.length]);
    }

    return colors;
}

/**
 * Update chart data dynamically
 * 
 * @param {Chart} chart - Chart.js instance
 * @param {array} labels - New labels
 * @param {array} data - New data
 */
function updateChartData(chart, labels, data) {
    if (!chart) {
        console.error('Chart instance not found');
        return;
    }

    chart.data.labels = labels;
    chart.data.datasets[0].data = data;
    chart.update();
}

/**
 * Destroy chart instance
 * 
 * @param {Chart} chart - Chart.js instance
 */
function destroyChart(chart) {
    if (chart) {
        chart.destroy();
    }
}

/**
 * Get responsive chart height based on screen size
 * 
 * @returns {number} Chart height in pixels
 */
function getResponsiveChartHeight() {
    const width = window.innerWidth;
    
    if (width < 768) {
        // Mobile
        return 250;
    } else if (width < 1024) {
        // Tablet
        return 300;
    } else {
        // Desktop
        return 350;
    }
}

/**
 * Initialize all charts on page load
 */
document.addEventListener('DOMContentLoaded', function() {
    // Set default Chart.js configuration
    if (typeof Chart !== 'undefined') {
        Chart.defaults.font.family = "'Inter', sans-serif";
        Chart.defaults.color = '#6b7280';
        Chart.defaults.borderColor = 'rgba(0, 0, 0, 0.1)';
    }

    // Handle window resize for responsive charts
    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            // Charts will automatically resize due to responsive: true
            console.log('Window resized - charts adjusting');
        }, 250);
    });
});

/**
 * Export utility functions
 */
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        chartColors,
        formatCurrency,
        formatNumber,
        createLineChart,
        createBarChart,
        createPieChart,
        createDoughnutChart,
        generateColorPalette,
        updateChartData,
        destroyChart,
        getResponsiveChartHeight
    };
}