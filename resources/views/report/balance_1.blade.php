<!DOCTYPE html>
<html lang="ru">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Текущий остаток ОС</title>
    <style>
        table, td {
            border: 1px solid #000;
        }

        table {
            border-collapse: collapse;
        }
    </style>
</head>
<body>
	<table border="1">
		<tr>
			<td style="font-weight:bold;"><strong>Наименование оборудования</strong></td>
			<td style="font-weight:bold;"><strong>Остаток на начало периода</strong></td>
		</tr>
		@foreach($rows as $row)
		<tr>
			<td>{{ $row->name }}</td>
			<td>{{ $row->balance_at_the_start }}</td>
		</tr>
		@endforeach
	</table>
</body>
</html>