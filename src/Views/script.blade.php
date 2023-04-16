<script type="text/javascript">
    const {{ $metric->id }}_ranges = JSON.parse('{!! json_encode($metric->ranges()) !!}');

    if ({{ $metric->id }}_ranges.length !== 0) {
        var {{ $metric->id }}_increaseOrDecrease = 0;
        var {{ $metric->id }}_first_range = Object.keys({{ $metric->id }}_ranges)[0];
    }

    let {{ $metric->id }}_create = function (url) {

        document.getElementById("{{ $metric->id }}_value").value = 0;
        document.getElementById("{{ $metric->id }}_load").style.display = 'block';
        document.getElementById("{{ $metric->id }}_metric").style.display = 'none';
        if (typeof url !== 'undefined') {
            url = "/metric-api/metrics/{{ $metric->uriKey() }}";
        }

        var queryString = {{ $metric->id }}_ranges.length !== 0 ? '?' + new URLSearchParams({
                range: {{ $metric->id }}_first_range
            }) : '';

        fetch(url+queryString)
            .then(data => data.json())
            .then(data => {

                let prefix = data.metric.prefix ?? '';
                let suffix = data.metric.suffix ?? '';

                if ({{ $metric->id }}_ranges.length !== 0) {
                {{ $metric->id }}_increaseOrDecrease = this.calculateIncreaseOrDecrease(data);
                    document.getElementById("{{ $metric->id }}_growth_percentage").innerHTML = {{ $metric->id }}_increaseOrDecrease +'% ' + this.increaseOrDecreaseLabel();
                }

                document.getElementById("{{ $metric->id }}_value").innerHTML = prefix +' '+ data.metric.value +' '+ suffix;
                document.getElementById("{{ $metric->id }}_load").style.display = 'none';
                document.getElementById("{{ $metric->id }}_metric").style.display = 'block';
            });
    };

    if ({{ $metric->id }}_ranges.length !== 0) {
        let {{ $metric->id }}_refresh = function (event) {
            document.getElementById("{{ $metric->id }}_value").innerHTML = '';
            document.getElementById("{{ $metric->id }}_metric").style.display = 'none';
            document.getElementById("{{ $metric->id }}_load").style.display = 'block';
            const url = "/metric-api/metrics/{{ $metric->uriKey() }}";
            fetch(url+'?' + new URLSearchParams({
                    range: event.target.value
                }))
                .then(data => data.json())
                .then(data => {

                    let prefix = data.metric.prefix ?? '';
                    let suffix = data.metric.suffix ?? '';

                    {{ $metric->id }}_increaseOrDecrease = this.calculateIncreaseOrDecrease(data);
                    document.getElementById("{{ $metric->id }}_growth_percentage").innerHTML = {{ $metric->id }}_increaseOrDecrease +'% ' + this.increaseOrDecreaseLabel();
                    document.getElementById("{{ $metric->id }}_value").innerHTML = prefix +' '+ data.metric.value +' '+ suffix;
                    document.getElementById("{{ $metric->id }}_load").style.display = 'none';
                    document.getElementById("{{ $metric->id }}_metric").style.display = 'block';

                });
        };

        function calculateIncreaseOrDecrease(result) {
        if (result.metric.previous == 0 || result.metric.previous == null || result.metric.value == 0)
            return 0

        return (((result.metric.value - result.metric.previous) / result.metric.previous) * 100).toFixed(2)
        }

        function increaseOrDecreaseLabel() {
        switch (Math.sign({{ $metric->id }}_increaseOrDecrease)) {
            case 1:
            return "{{ __('Increase') }}"
            case 0:
            return "{{ __('Constant') }}"
            case -1:
            return "{{ __('Decrease') }}"
        }
        }
        const {{ $metric->id }}_selectElement = document.getElementById("{{ $metric->id }}_select");

        {{ $metric->id }}_selectElement.addEventListener("change", (event) => {
            {{ $metric->id }}_refresh(event)
        });
    }

    window.addEventListener("load", {{ $metric->id }}_create);
   
</script>