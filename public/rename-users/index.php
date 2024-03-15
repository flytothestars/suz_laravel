<!DOCTYPE html>
<html lang="ru">
<head>
	<meta charset="UTF-8">
	<title>Переименование пользователей</title>
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
	<link rel="stylesheet" href="../css/matrix.css">
</head>
<body>
	<div id="info">
		<div class="row justify-content-center">
			<div class="col-md-8">
				<form id="form">
					<div class="row align-items-center mb-5">
						<div class="col-md-6">
							<label>Старые учетки</label>
							<textarea class="form-control" name="old_usernames" id="old_usernames" placeholder="Вставьте старые username построчно"></textarea>
						</div>
						<div class="col-md-6">
							<label>Новые учетки</label>
							<textarea class="form-control" name="new_usernames" id="new_usernames" placeholder="Вставьте новые username построчно"></textarea>
						</div>
					</div>
					<button type="button" class="btn btn-success btn-lg" id="start">Начать замену</button>
					<div class="text-center">
						<div class="mt-5 result alert mx-auto"></div>
					</div>
				</form>
			</div>
		</div>
	</div>
	<script src="../vendor/jquery/dist/jquery.min.js"></script>
	<script>
		$(document).ready(function(){
			$("#start").on("click", function(){
				let old_usernames = $('#old_usernames').val();
				let new_usernames = $('#new_usernames').val();
				if(old_usernames.length > 0 && new_usernames.length > 0){
					$.ajax({
						url: 'script.php',
						type: 'post',
						data: {
							old_usernames: old_usernames,
							new_usernames: new_usernames
						},
						dataType: "json",
						success(data){
							if(data['success'] == true){
								$("#form").find(".result").removeClass('alert-danger');
								$("#form").find(".result").addClass('alert-success');
							}else{
								$("#form").find(".result").removeClass('alert-success');
								$("#form").find(".result").addClass('alert-danger');
							}
							$("#form").find('.result').show().html(data['html']);
						}
					});
				}
			});
		});
	</script>
</body>
</html>