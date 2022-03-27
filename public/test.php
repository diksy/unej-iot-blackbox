<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="author" content="Diksy M. Firmansyah <diksy@unej.ac.id>" />

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">

    <!-- Font Awesome -->
    <link href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" rel="stylesheet" integrity="sha384-DyZ88mC6Up2uqS4h/KRgHuoeGwBcD4Ng9SiP4dIRy0EXTlnuz47vAwmeGwVChigm" crossorigin="anonymous" />

    <link rel="icon" type="image/x-icon" href="favicon.ico" />
    <title>UNEJ IoT Blackbox</title>
  </head>
  <body class="bg-light">
    <div class="container my-3">
        <form>
          <fieldset disabled="disabled">
              <div class="row mb-3">
                <label for="temperature" class="col-sm-2 col-form-label">Temperature</label>
                <div class="col-sm-10">
                  <input type="number" class="form-control" name="temperature" id="temperature" step="0.01" required="required" />
                </div>
              </div>
              <div class="row mb-3">
                <label for="humidity" class="col-sm-2 col-form-label">Humidity</label>
                <div class="col-sm-10">
                  <input type="number" class="form-control" name="humidity" id="humidity" step="0.01" required="required" />
                </div>
              </div>
              <div class="row mb-3">
                <label for="created_at" class="col-sm-2 col-form-label">Datetime</label>
                <div class="col-sm-10">
                  <input type="datetime-local" class="form-control" name="created_at" id="created_at" required="required" />
                </div>
              </div>
              <button type="submit" class="btn btn-primary"><span class="spinner-grow spinner-grow-sm" role="status" aria-hidden="true"></span> Loading...</button>
            </form>
          </fieldset>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>

    <script>
        $(document).ready(function () {
            if (localStorage.getItem('authorization') != null) {
                $.ajaxSetup({
                    headers: {
                        "Authorization": localStorage.getItem("authorization")
                    }
                });
                $("form button[type=submit]").html("<i class='fas fa-paper-plane me-2'></i>Submit");
                $("form fieldset").removeAttr("disabled");
            } else {
                $.post("http://192.168.1.3:8888/unej-blackbox/public/api/signin", { email: "diksy@unej.ac.id", password: "secretxx" }, function (response) {
                    localStorage.setItem("authorization", response.authorization);
                    $.ajaxSetup({
                        headers: {
                            "Authorization": localStorage.getItem("authorization")
                        }
                    });
                    $("form button[type=submit]").html("<i class='fas fa-paper-plane me-2'></i>Submit");
                    $("form fieldset").removeAttr("disabled");
                }).fail(function (xhr, status, error) {
                    $("form").prepend("<div class='alert alert-danger alert-dismissible fade show' role='alert'><strong>Error " + xhr.status + "!</strong> " + error + "<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button></div>");
                    $("form button[type=submit]").html("<i class='fas fa-xmark me-2'></i>Error");
                });
            }
            $("form").submit(function (event) {
                event.preventDefault();
                var data = $(this).serialize();
                $(this).find("fieldset").attr("disabled", "disabled");
                $(this).find("button[type=submit]").html("<span class='spinner-grow spinner-grow-sm' role='status' aria-hidden='true'></span> Loading...");
                $.post("http://192.168.1.3:8888/unej-blackbox/public/api/dht", data, function (response) {
                    $("form").prepend("<div class='alert alert-success alert-dismissible fade show' role='alert'><strong>Success!</strong> Data has been added successfully.<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button></div>");
                    $("form button[type=submit]").html("<i class='fas fa-check me-2'></i>Success");
                }).fail(function (xhr, status, error) {
                    $("form").prepend("<div class='alert alert-danger alert-dismissible fade show' role='alert'><strong>Error " + xhr.status + "!</strong> " + error + "<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button></div>");
                    $("form button[type=submit]").html("<i class='fas fa-paper-plane me-2'></i>Submit");
                    $("form fieldset").removeAttr("disabled");
                });
            });
        });
    </script>
  </body>
</html>
