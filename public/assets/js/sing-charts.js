/**
 * Sing App Adaptive Charts Engine (ApexCharts Integration)
 * Matches Flatlogic Sing App Vue Dashboard analytics specs
 * Supports period switching (Daily, Weekly, Monthly) and dynamic Dark/Light theme sync
 */

(function () {
  'use strict';

  // Keep track of initialized chart instances
  const chartInstances = {};

  // Period datasets for Main Analytics Chart
  const mainChartData = {
    daily: {
      categories: ['00:00', '03:00', '06:00', '09:00', '12:00', '15:00', '18:00', '21:00'],
      visits: [120, 90, 150, 480, 620, 550, 710, 430],
      revenue: [45, 30, 60, 210, 380, 290, 440, 260]
    },
    weekly: {
      categories: ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'],
      visits: [2400, 3200, 2800, 4100, 4800, 3900, 4300],
      revenue: [1200, 1850, 1400, 2600, 3100, 2400, 2900]
    },
    monthly: {
      categories: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
      visits: [31000, 40000, 28000, 51000, 42000, 109000, 100000, 120000, 85000, 95000, 140000, 160000],
      revenue: [11000, 32000, 45000, 32000, 34000, 52000, 41000, 75000, 60000, 80000, 95000, 110000]
    }
  };

  /**
   * Helper to get theme-aware chart colors and configuration
   */
  function getThemeChartConfig() {
    const isDark = document.documentElement.getAttribute('data-theme') === 'dark';

    return {
      mode: isDark ? 'dark' : 'light',
      foreColor: isDark ? '#94a3b8' : '#64748b',
      gridBorder: isDark ? '#1e293b' : '#e2e8f0',
      tooltipTheme: isDark ? 'dark' : 'light',
      cardBg: isDark ? '#151e32' : '#ffffff'
    };
  }

  /**
   * 1. Main Analytics Area Chart (Visits & Revenue with Period Tabs)
   */
  function initMainAnalyticsChart(elementId, initialPeriod = 'monthly') {
    const el = document.getElementById(elementId);
    if (!el || typeof ApexCharts === 'undefined') return;

    const themeCfg = getThemeChartConfig();
    const data = mainChartData[initialPeriod] || mainChartData.monthly;

    const options = {
      series: [
        {
          name: 'Visitas',
          data: data.visits
        },
        {
          name: 'Ingresos ($)',
          data: data.revenue
        }
      ],
      chart: {
        height: 330,
        type: 'area',
        toolbar: {
          show: true,
          tools: {
            download: true,
            selection: false,
            zoom: false,
            zoomin: false,
            zoomout: false,
            pan: false,
            reset: false
          }
        },
        background: 'transparent',
        foreColor: themeCfg.foreColor,
        fontFamily: 'Inter, sans-serif',
        animations: {
          enabled: true,
          easing: 'easeinout',
          speed: 600
        }
      },
      colors: ['#4d7cfe', '#22c55e'],
      fill: {
        type: 'gradient',
        gradient: {
          shadeIntensity: 1,
          opacityFrom: 0.4,
          opacityTo: 0.05,
          stops: [0, 90, 100]
        }
      },
      dataLabels: { enabled: false },
      stroke: {
        curve: 'smooth',
        width: 3
      },
      grid: {
        borderColor: themeCfg.gridBorder,
        strokeDashArray: 4,
        xaxis: { lines: { show: false } }
      },
      xaxis: {
        categories: data.categories,
        axisBorder: { show: false },
        axisTicks: { show: false }
      },
      yaxis: {
        labels: {
          formatter: val => {
            if (val >= 1000) return `$${(val / 1000).toFixed(0)}k`;
            return `$${val}`;
          }
        }
      },
      tooltip: {
        theme: themeCfg.tooltipTheme,
        x: { show: true }
      },
      legend: {
        position: 'top',
        horizontalAlign: 'right',
        labels: { colors: themeCfg.foreColor },
        markers: { radius: 12 }
      }
    };

    if (chartInstances[elementId]) {
      chartInstances[elementId].destroy();
    }

    chartInstances[elementId] = new ApexCharts(el, options);
    chartInstances[elementId].render();

    // Hook period button switchers
    const periodButtons = document.querySelectorAll('[data-chart-period]');
    periodButtons.forEach(btn => {
      btn.addEventListener('click', function (e) {
        e.preventDefault();
        periodButtons.forEach(b => b.classList.remove('active'));
        this.classList.add('active');

        const period = this.getAttribute('data-chart-period');
        switchMainChartPeriod(elementId, period);
      });
    });
  }

  /**
   * Switch data period for Main Analytics Chart
   */
  function switchMainChartPeriod(elementId, period) {
    const chart = chartInstances[elementId];
    const data = mainChartData[period];
    if (!chart || !data) return;

    chart.updateOptions({
      xaxis: { categories: data.categories }
    });

    chart.updateSeries([
      { name: 'Visitas', data: data.visits },
      { name: 'Ingresos ($)', data: data.revenue }
    ]);
  }

  /**
   * 2. Traffic Sources Donut Chart
   */
  function initMarketShareChart(elementId) {
    const el = document.getElementById(elementId);
    if (!el || typeof ApexCharts === 'undefined') return;

    const themeCfg = getThemeChartConfig();

    const options = {
      series: [45, 25, 18, 12],
      chart: {
        type: 'donut',
        height: 290,
        background: 'transparent',
        foreColor: themeCfg.foreColor,
        fontFamily: 'Inter, sans-serif'
      },
      labels: ['Tráfico Directo', 'Búsqueda Orgánica', 'Redes Sociales', 'Campañas Referidas'],
      colors: ['#4d7cfe', '#22c55e', '#8b5cf6', '#f59e0b'],
      plotOptions: {
        pie: {
          donut: {
            size: '72%',
            labels: {
              show: true,
              total: {
                show: true,
                label: 'Total Tráfico',
                color: themeCfg.foreColor,
                fontSize: '13px',
                fontWeight: 600,
                formatter: () => '100%'
              }
            }
          }
        }
      },
      stroke: {
        show: true,
        colors: [themeCfg.cardBg],
        width: 2
      },
      dataLabels: { enabled: false },
      legend: {
        position: 'bottom',
        labels: { colors: themeCfg.foreColor }
      },
      tooltip: {
        theme: themeCfg.tooltipTheme
      }
    };

    if (chartInstances[elementId]) {
      chartInstances[elementId].destroy();
    }

    chartInstances[elementId] = new ApexCharts(el, options);
    chartInstances[elementId].render();
  }

  /**
   * 3. Performance / Department Bar Chart
   */
  function initPerformanceBarChart(elementId) {
    const el = document.getElementById(elementId);
    if (!el || typeof ApexCharts === 'undefined') return;

    const themeCfg = getThemeChartConfig();

    const options = {
      series: [
        { name: 'Ventas Reales', data: [44, 55, 57, 56, 61, 58, 63, 60, 66] },
        { name: 'Objetivo Proyectado', data: [76, 85, 101, 98, 87, 105, 91, 114, 94] }
      ],
      chart: {
        type: 'bar',
        height: 320,
        toolbar: { show: false },
        background: 'transparent',
        foreColor: themeCfg.foreColor,
        fontFamily: 'Inter, sans-serif'
      },
      plotOptions: {
        bar: {
          horizontal: false,
          columnWidth: '45%',
          borderRadius: 6
        }
      },
      dataLabels: { enabled: false },
      stroke: {
        show: true,
        width: 2,
        colors: ['transparent']
      },
      colors: ['#4d7cfe', '#8b5cf6'],
      xaxis: {
        categories: ['Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct'],
        axisBorder: { show: false },
        axisTicks: { show: false }
      },
      grid: {
        borderColor: themeCfg.gridBorder,
        strokeDashArray: 4
      },
      tooltip: {
        theme: themeCfg.tooltipTheme
      },
      legend: {
        position: 'top',
        labels: { colors: themeCfg.foreColor }
      }
    };

    if (chartInstances[elementId]) {
      chartInstances[elementId].destroy();
    }

    chartInstances[elementId] = new ApexCharts(el, options);
    chartInstances[elementId].render();
  }

  /**
   * 4. Mini Sparklines for KPI Cards
   */
  function initSparkline(elementId, data, color) {
    const el = document.getElementById(elementId);
    if (!el || typeof ApexCharts === 'undefined') return;

    const options = {
      series: [{ data: data || [10, 15, 8, 22, 18, 30, 25] }],
      chart: {
        type: 'area',
        height: 48,
        sparkline: { enabled: true }
      },
      stroke: { curve: 'smooth', width: 2.5 },
      fill: {
        type: 'gradient',
        gradient: {
          shadeIntensity: 1,
          opacityFrom: 0.45,
          opacityTo: 0.05
        }
      },
      colors: [color || '#4d7cfe'],
      tooltip: { enabled: false }
    };

    const chart = new ApexCharts(el, options);
    chart.render();
  }

  /**
   * Global listener for Theme Change event
   * Automatically updates all active charts without page reloading
   */
  window.addEventListener('sing:theme-change', function (e) {
    const themeCfg = getThemeChartConfig();

    Object.keys(chartInstances).forEach(id => {
      const chart = chartInstances[id];
      if (chart) {
        chart.updateOptions({
          chart: { foreColor: themeCfg.foreColor },
          grid: { borderColor: themeCfg.gridBorder },
          tooltip: { theme: themeCfg.tooltipTheme },
          stroke: { colors: [themeCfg.cardBg] },
          legend: { labels: { colors: themeCfg.foreColor } }
        });
      }
    });
  });

  // Export to Global SingCharts
  window.SingCharts = {
    initMainAnalytics: initMainAnalyticsChart,
    initMarketShare: initMarketShareChart,
    initPerformanceBar: initPerformanceBarChart,
    initSparkline: initSparkline,
    switchPeriod: switchMainChartPeriod
  };
})();
