(function() {
  var chartEl = document.getElementById('chart-phase-priority');
  if (!chartEl || typeof echarts === 'undefined') {
    return;
  }

  var style = getComputedStyle(document.documentElement);
  var accent = style.getPropertyValue('--accent').trim();
  var accent2 = style.getPropertyValue('--accent2').trim();
  var ink = style.getPropertyValue('--ink').trim();
  var muted = style.getPropertyValue('--muted').trim();
  var rule = style.getPropertyValue('--rule').trim();
  var bg2 = style.getPropertyValue('--bg2').trim();

  var chart = echarts.init(chartEl, null, { renderer: 'svg' });
  chart.setOption({
    animation: false,
    backgroundColor: 'transparent',
    color: [accent, accent2],
    tooltip: {
      trigger: 'axis',
      appendToBody: true,
      axisPointer: { type: 'shadow' }
    },
    legend: {
      data: ['MVP Tahap 1', 'Fase Lanjutan'],
      textStyle: { color: muted }
    },
    grid: {
      top: 48,
      left: 120,
      right: 24,
      bottom: 32
    },
    xAxis: {
      type: 'value',
      axisLine: { lineStyle: { color: rule } },
      splitLine: { lineStyle: { color: rule } },
      axisLabel: { color: muted }
    },
    yAxis: {
      type: 'category',
      data: [
        'Master Data',
        'Barang Masuk',
        'Stock Gudang',
        'Barang Keluar',
        'Barang Tarikan',
        'Mutasi',
        'Laporan',
        'Aset & Approval',
        'Repair & Disposal'
      ],
      axisLine: { lineStyle: { color: rule } },
      axisTick: { show: false },
      axisLabel: { color: ink }
    },
    series: [
      {
        name: 'MVP Tahap 1',
        type: 'bar',
        barMaxWidth: 22,
        data: [5, 5, 5, 5, 4, 4, 4, 1, 1],
        itemStyle: { color: accent }
      },
      {
        name: 'Fase Lanjutan',
        type: 'bar',
        barMaxWidth: 22,
        data: [1, 1, 2, 2, 2, 3, 3, 5, 5],
        itemStyle: { color: accent2 }
      }
    ]
  });

  window.addEventListener('resize', function() {
    chart.resize();
  });
})();
