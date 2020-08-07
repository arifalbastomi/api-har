<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="Mark Otto, Jacob Thornton, and Bootstrap contributors">
    <meta name="generator" content="Jekyll v3.8.5">
    <title>Security</title>

    <link rel="canonical" href="https://getbootstrap.com/docs/4.3/examples/blog/">

    <!-- Bootstrap core CSS -->
<link href="https://getbootstrap.com/docs/4.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">


    <style>
      .bd-placeholder-img {
        font-size: 1.125rem;
        text-anchor: middle;
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
      }

      @media (min-width: 768px) {
        .bd-placeholder-img-lg {
          font-size: 3.5rem;
        }
      }
    </style>
    <!-- Custom styles for this template -->
    <link href="https://fonts.googleapis.com/css?family=Playfair+Display:700,900" rel="stylesheet">
    <!-- Custom styles for this template -->
  </head>
  <body>
    <div class="container">
  <header class="blog-header py-3">
    <div class="row flex-nowrap justify-content-between align-items-center">
      <div class="col-12 text-center">
        <h3 class="pb-4 mb-4 font-italic border-bottom">Security</h3>
      </div>
    </div>
  </header>

<main role="main" class="container">
  <div class="row">
    <div class="col-md-12 blog-main">
      <div class="blog-post">
		<form class="form-signin">
		  <label for="key" class="sr-only">Key</label>
		  <input type="text" id="key" class="form-control" placeholder="Key" required="" autofocus="" style="margin-bottom:10px;">
		  <label for="iv" class="sr-only">Iv</label>
		  <input type="text" id="iv" class="form-control" placeholder="Iv" required="" style="margin-bottom:10px;">
		  <label for="data" class="sr-only">Data</label>
		  <textarea class="form-control" rows="5" id="data" placeholder="Data" style="margin-bottom:10px;"></textarea>
		  <select class="form-control" id="type" onchange="ubah_type(this)" style="margin-bottom:10px;">
			<option value="encrypt" selected>Encrypt</option>
			<option value="decrypt">Decrypt</option>
		  </select>
		  <label id='hasils' style="margin-bottom:10px;">Hasil Encrypt : </label>
		  <textarea class="form-control" rows="5" id="hasil" placeholder="Hasil" readonly></textarea><br>
		  <input type="hidden" id="hasilss" class="form-control" placeholder="hasilss" required="" value="encrypt" style="margin-bottom:10px;">
		  <button class="btn btn-lg btn-primary btn-block" id="security" onclick="submits()" type="button">Encrypt</button><br>
		</form>

      </div><!-- /.blog-post -->
  </div><!-- /.row -->

</main><!-- /.container -->
<script src="https://api-dev.omegasoft.co.id/assets/js/jquery-1.10.2.js"></script>
<script>
	function ubah_type(selectObj) {
		var selectIndex=selectObj.selectedIndex;
		var selectValue=selectObj.options[selectIndex].text;
		var output=document.getElementById("security");
		//alert(output.innerText);
		output.innerHTML=selectValue;
		$("#hasils").text('Hasil '+selectValue+':');
		$('#hasilss').val(selectValue.toLowerCase());
	}
	
	function submits(){
		var key  = $('#key').val();
		var iv   = $('#iv').val();
		var data = $('#data').val();
		var type = $('#hasilss').val();
		
		$.ajax({
			   url : 'https://api-dev.omegasoft.co.id/v1/security',
			   headers : {
					'token': '178295471927491765929471'
			   },
			   type : 'POST',
			   data : 'data='+data+'&key='+key+'&iv='+iv+'&type='+type,
			   success : function(data) {
				document.getElementById("hasil").innerHTML=data;
			   }
		});
	}
</script>
</body>
</html>