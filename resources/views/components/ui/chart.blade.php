@props(['type' => 'bar', 'data' => [], 'options' => []])

<div {{ $attributes->class(['relative']) }}>
    <canvas
        x-data
        x-init="
            new Chart($el, {
                type: @js($type),
                data: @js($data),
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { labels: { boxWidth: 10, font: { size: 11 } } } },
                    ...@js($options),
                },
            })
        "
    ></canvas>
</div>
