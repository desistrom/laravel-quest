<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laravel 8 Bootstrap 5 Progress Bar Example</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
      <div class="alert alert-warning mb-4 text-center">
        <h2 class="display-6">Upload File CSV</h2>
      </div>
        <form id="fileUploadForm" method="POST" action="{{ url('/upload-doc-file') }}" enctype="multipart/form-data">
            @csrf
            <div class="row">
              <div class="col-sm">
                <input name="file" type="file" class="form-control">
              </div>
              <div class="col-sm">
                <input type="submit" value="Submit" class="btn btn-primary">
              </div>
            </div>
            <div class="form-group">
                <div class="progress" style="display: none;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-danger" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%"></div>
                </div>
            </div>
        </form>
        <div>
        <table class="table">
          <thead>
            <tr>
              <th scope="col">Time</th>
              <th scope="col">File name</th>
              <th scope="col">Status</th>
            </tr>
          </thead>
          <tbody>
          @foreach ($dataFile as $value)
            <tr>
            <td id="data-{{$value->id}}">{{ $value->upload_at }}</td>
            <td>{{ $value->name }}</td>
            <td>{{ $value->status }}</td>
            </tr>
          @endforeach
          </tbody>
        </table>
      </div>
    </div>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.form/4.3.0/jquery.form.min.js"></script>
    <!-- <script type="text/javascript">
        window.laravel_echo_hostname = "{{ env('LARAVEL_ECHO_HOSTNAME') }}";
    </script>
    <script src="{{ env('LARAVEL_ECHO_HOSTNAME') }}/socket.io/socket.io.js" type="text/javascript"></script>
    <script src="{{ mix('js/app.js') }}" type="text/javascript"></script>
    <script type="text/javascript">
        var i = 0;
        window.Echo.channel('user-channel').listen('UserEvent', (data) => {
            i++;
            console.log(data);
            // $("table tbody").append('<td></td><td>'+data.title+'</div>');
            $("#notification").append('<div class="alert alert-success">'+i+'. '+data.title+'</div>');

        });
    </script> -->
    <script src="https://js.pusher.com/7.0/pusher.min.js"></script>
    <script>

      // Enable pusher logging - don't include this in production
      Pusher.logToConsole = true;

      var pusher = new Pusher('7d6a11e78f659c7b8006', {
        cluster: 'ap1'
      });

      var channel = pusher.subscribe('user-channel');
      channel.bind('UserEvent', function(data) {
        // alert(JSON.stringify(data));
        $("table tbody").append('<tr><td>'+data.time+'</td><td>'+data.name+'</td><td>'+data.status+'</td></tr>');
      });
    </script>
    <script>
        $(function () {
            $(document).ready(function () {
                $('#fileUploadForm').ajaxForm({
                    beforeSend: function () {
                        $('.progress-bar').removeClass('bg-success');
                        $('.progress-bar').addClass('bg-danger');
                        $('.progress').show(1000);
                        var percentage = '0';
                    },
                    uploadProgress: function (event, position, total, percentComplete) {
                        var percentage = percentComplete;
                        $('.progress .progress-bar').css("width", percentage+'%')
                    },
                    complete: function (xhr) {
                        console.log('File has uploaded');
                        $('.progress-bar').removeClass('bg-danger');
                        $('.progress-bar').addClass('bg-success');
                        setTimeout(function(){
                          $('.progress').hide(1000);
                        }, 3000)
                    }
                });
            });
        });
    </script>
</body>
</html>