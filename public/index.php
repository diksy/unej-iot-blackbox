<?php
    require_once('../db.php');

    // set where statement
    $where = [];
    if (isset($_GET['begin']) && strlen($_GET['begin']) > 0) {
        $begin = new DateTime($_GET['begin'], new DateTimeZone('Asia/Jakarta'));
        array_push($where, 'created_at >= "' . $begin->format('Y-m-d H:i:s') . '"');
    }
    if (isset($_GET['end']) && strlen($_GET['end']) > 0) {
        $end = new DateTime($_GET['end'], new DateTimeZone('Asia/Jakarta'));
        array_push($where, 'created_at <= "' . $end->format('Y-m-d H:i:s') . '"');
    }
    if (count($where) > 0) {
        $limit = count($where) > 1 ? false : true;
        $where = 'where ' . implode(' and ', $where);
    } else {
        $limit = true;
        $where = '';
    }

    // set database query
    switch ($_GET['timeframe']) {
        case 'Daily':
            $limit = $limit ? 'limit 30' : '';
            $sql   = $db->query("
                select
                    round(avg(temperature), 2) as temperature,
                    round(avg(humidity), 2) as humidity,
                    min(created_at) as created_at
                from sensor_dht
                {$where}
                group by substr(created_at, 1, 10)
                order by min(created_at) desc
                {$limit}
            ");
            break;
        case 'Hourly':
            $limit = $limit ? 'limit 48' : '';
            $sql   = $db->query("
                select
                    round(avg(temperature), 2) as temperature,
                    round(avg(humidity), 2) as humidity,
                    min(created_at) as created_at
                from sensor_dht
                {$where}
                group by substr(created_at, 1, 13)
                order by min(created_at) desc
                {$limit}
            ");
            break;
        default:
            $limit = $limit ? 'limit 60' : '';
            $sql   = $db->query("
                select
                    temperature,
                    humidity,
                    created_at
                from sensor_dht
                {$where}
                order by created_at desc
                {$limit}
            ");
            break;
    }

    // set data results
    $data = [
        'temperature' => [],
        'humidity'    => [],
        'labels'      => []
    ];
    if ($sql->num_rows > 0) {
        while ($row = $sql->fetch_assoc()) {
            $row['created_at'] = new DateTime($row['created_at'], new DateTimeZone('Asia/Jakarta'));
            array_push($data['temperature'], $row['temperature']);
            array_push($data['humidity'], $row['humidity']);
            array_push($data['labels'], "'{$row['created_at']->format('Y-m-d\TH:i:s')}.000Z'");
        }
    }
    $db->close();
?>
<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="author" content="Diksy M. Firmansyah <diksy@unej.ac.id>" />

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous" />

    <!-- Font Awesome -->
    <link href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" rel="stylesheet" integrity="sha384-DyZ88mC6Up2uqS4h/KRgHuoeGwBcD4Ng9SiP4dIRy0EXTlnuz47vAwmeGwVChigm" crossorigin="anonymous" />

    <link rel="icon" type="image/x-icon" href="favicon.ico" />
    <title>UNEJ IoT BlackBox</title>
  </head>
  <body class="bg-light">
    <div class="container-fluid my-3">
        <div class="row">
            <div class="col-xl-6 mb-3">
                <div class="card">
                  <h5 class="card-header"><i class="fas fa-temperature-low"></i> Temperature <sub><?=$data['temperature'][0]?><sup>o</sup>C</sub></h5>
                  <div class="card-body" id="temperature">
                    <div class="spinner-border spinner-border-sm" role="status">
                      <span class="visually-hidden">Loading...</span>
                    </div> Loading
                  </div>
                </div>
            </div>
            <div class="col-xl-6 mb-3">
                <div class="card">
                  <h5 class="card-header"><i class="fas fa-tint"></i> Humidity <sub><?=$data['humidity'][0]?>%</sub></h5>
                  <div class="card-body" id="humidity">
                    <div class="spinner-border spinner-border-sm" role="status">
                      <span class="visually-hidden">Loading...</span>
                    </div> Loading
                  </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <div class="card">
                    <div class="card-body">
                        <form class="row row-cols-lg-auto g-3 align-items-center">
                          <div class="col-12">
                            <label class="visually-hidden" for="timeframe">Timeframe</label>
                            <select class="form-select" id="timeframe" name="timeframe">
                              <option<?=( ! isset($_GET['timeframe']) || $_GET['timeframe'] == 'Minutely') ? ' selected="selected"' : ''?>>Minutely</option>
                              <option<?=$_GET['timeframe'] == 'Hourly' ? ' selected="selected"' : ''?>>Hourly</option>
                              <option<?=$_GET['timeframe'] == 'Daily' ? ' selected="selected"' : ''?>>Daily</option>
                            </select>
                          </div>

                          <div class="col-12">
                            <label class="visually-hidden" for="begin">Begin</label>
                            <div class="input-group">
                              <div class="input-group-text">From:</div>
                              <input type="datetime-local" class="form-control" id="begin" name="begin" value="<?=isset($_GET['begin']) ? $_GET['begin'] : ''?>" />
                            </div>
                          </div>

                          <div class="col-12">
                            <label class="visually-hidden" for="end">End</label>
                            <div class="input-group">
                              <div class="input-group-text">To:</div>
                              <input type="datetime-local" class="form-control" id="end" name="end" value="<?=isset($_GET['end']) ? $_GET['end'] : ''?>" />
                            </div>
                          </div>

                          <div class="col-12">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Apply</button>
                          </div>

                          <div class="col-12">
                            <div class="form-check">
                              <input class="form-check-input" type="checkbox" id="refresh" name="refresh" value="1"<?=isset($_GET['refresh']) && $_GET['refresh'] == 1 ? ' checked="checked"' : ''?> />
                              <label class="form-check-label" for="refresh">
                                5 minute refresh
                              </label>
                            </div>
                          </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        $(document).ready(function () {
            // temperature chart
            var temperature = {
                series: [{
                  name: 'Celcius',
                  data: [<?=implode(',', array_reverse($data['temperature']))?>]
                }],
                chart: {
                  height: 350,
                  type: 'area',
                  zoom: {
                      enabled: false
                  }
                },
                dataLabels: {
                  enabled: false
                },
                stroke: {
                  curve: 'smooth'
                },
                xaxis: {
                  type: 'datetime',
                  categories: [<?=implode(',', array_reverse($data['labels']))?>]
                },
                yaxis: {
                    opposite: true,
                    forceNiceScale: true,
                    title: {
                        text: "Celcius (C)"
                    }
                },
                tooltip: {
                  x: {
                    format: 'dd/MM/yy HH:mm'
                  },
                },
            };
            $("#temperature").html("");
            var chart01 = new ApexCharts(document.querySelector("#temperature"), temperature);
            chart01.render();

            // humidity chart
            var humidity = {
                series: [{
                  name: 'Percent',
                  data: [<?=implode(',', array_reverse($data['humidity']))?>]
                }],
                chart: {
                  height: 350,
                  type: 'area',
                  zoom: {
                      enabled: false
                  }
                },
                dataLabels: {
                  enabled: false
                },
                stroke: {
                  curve: 'smooth'
                },
                xaxis: {
                  type: 'datetime',
                  categories: [<?=implode(',', array_reverse($data['labels']))?>]
                },
                yaxis: {
                    opposite: true,
                    forceNiceScale: true,
                    title: {
                        text: "Percent (%)"
                    }
                },
                tooltip: {
                  x: {
                    format: 'dd/MM/yy HH:mm'
                  },
                },
            };
            $("#humidity").html("");
            var chart02 = new ApexCharts(document.querySelector("#humidity"), humidity);
            chart02.render();

            $("#refresh").change(function () {
                $("form").submit();
            });
            <?php if (isset($_GET['refresh']) && $_GET['refresh'] == 1): ?>
            window.setTimeout(function () {
                location.reload();
            }, 300000);
            <?php endif; ?>
        });
    </script>
  </body>
</html>
