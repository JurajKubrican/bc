<html>
  <head>
    <script src="js/jquery.js" charset="utf-8"></script>
    <script src="js/bootstrap.min.js" charset="utf-8"></script>
    <link href="/css/bootstrap.min.css" rel="stylesheet">

    <link ''>
  </head>
  <body>

    <div class="container">
      <div class="row">
        <div class="col-md-5">
          <button type="button" class="btn btn-primary" value="" name="button" onclick="refreshAll('all');">Refresh All</button>
          <br>
          <iframe src="place/crawl?all" width="" height=""></iframe>
        </div>
        <div class="col-md-5">
          <button type="button" class="btn btn-primary" name="button" onclick="refreshSome('some');">Refresh some</button>
          <br>
          <iframe src="place/crawl?some" width="" height=""></iframe>
        </div>
      </div>


    </div>
    <script>
    refreshAll

    refreshSome

    </script>

  </body>

</html>
