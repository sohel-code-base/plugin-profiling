<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Dashboard | Plugin Profiling</title>

    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="{{ asset('js/Chart.js') }}"></script>
    @vite('resources/js/app.js')
    <style>
        #active-version {
          width: 100%;
          height: 500px;
          max-width: 100%
        }

        #download-history {
          width: 100%;
          height: 500px;
          max-width: 100%
        }
        </style>
</head>
<body>
    <div class="container">
        <div class="row text-center">
            <div class="col">
                <h3>Overview</h3>
            </div>
        </div>

        <form id="filter-form" action="{{ route('plugin.details') }}" method="GET">
            @csrf
            <div class="row bg-secondary p-2">
                <div class="col-md-3">
                    <label for="">Start Date:</label>
                    <input type="text" name="start_date" autocomplete="on" class="form-control datepicker" id="start_date" placeholder="yyyy/mm/dd"/>
                </div>
                <div class="col-md-3">
                    <label for="">End Date:</label>
                    <input type="text" name="end_date" autocomplete="on" class="form-control datepicker" id="end_date" placeholder="yyyy/mm/dd"/>
                </div>
                <div class="col-md-3">
                    <label for="">Plugin</label>
                    <select name="plugin" id="plugin" class="form-control">
                        <option value="classic-editor">Classic Editor</option>
                        <option value="contact-form-7">Contact Form 7</option>
                        <option value="woocommerce">Woocommerce</option>
                        <option value="wordpress-seo">Yoast SEO</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary mt-4" id="search">Search</button>
                </div>
            </div>
        </form>

        <div class="row" id="chart-area">
            <div class="col-md-6">
                {{-- <h3>ACTIVE VERSION</h3> --}}
                <canvas id="active-version" style="width:100%;max-width:600px"></canvas>
            </div>
            <div class="col-md-6">
                {{-- <h3>DOWNLOAD HISTORY</h3> --}}
                <canvas id="download-history" style="width:100%;max-width:600px"></canvas>
            </div>
        </div>
    </div>



    <script>
        $(document).ready(function(){
            $('#search').on('click', function(e){
                e.preventDefault();
                const start_date = $("#start_date").val();
                const end_date = $("#end_date").val();
                const plugin = $("#plugin").val();
                const action = $("form").attr("action");

                // console.log(start_date, end_date, plugin);
                $.ajax({
                    type: "get",
                    url: action,
                    data: {start_date: start_date, end_date: end_date, plugin: plugin},
                    success: function (res) {
                        
                        var barColors = ["red", "green","blue","orange","brown"];

                        new Chart("active-version", {
                        type: "bar",
                        data: {
                            labels: res.version.number,
                            datasets: [{
                            backgroundColor: barColors,
                            data: res.version.percentage
                            }]
                        },
                        options: {
                            legend: {display: false},
                            title: {
                            display: true,
                            text: "ACTIVE VERSION"
                            }
                        });

                        new Chart("download-history", {
                        type: "line",
                        data: {
                            labels: res.download.date,
                            datasets: [{
                            fill: false,
                            // lineTension: 0,
                            // backgroundColor: "rgba(0,0,255,1.0)",
                            borderColor: "blue",
                            data: res.download.value
                            }]
                        },
                        options: {
                            legend: {display: false},
                            title: {
                            display: true,
                            text: "DOWNLOAD HISTORY"
                            }
                         
                        }
                        });

                    }
                })
            })
        })



    </script>
</body>
</html>