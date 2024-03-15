<!DOCTYPE html>
<html lang="ru">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Детализация ТМЦ</title>
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
			<td style="font-weight:bold;"><strong>Филиал</strong></td>
			<td style="font-weight:bold;"><strong>Город</strong></td>
			<td style="font-weight:bold;"><strong>Адрес</strong></td>
			<!-- <td style="font-weight:bold;"><strong>Склад</strong></td> -->
			<td style="font-weight:bold;"><strong>Участок</strong></td>
			<td style="font-weight:bold;"><strong>Вид деятельности</strong></td>
			<td style="font-weight:bold;"><strong>Лицевой счет</strong></td>
			<td style="font-weight:bold;"><strong>Номер наряда</strong></td>
			<td style="font-weight:bold;"><strong>Дата</strong></td>
			<td style="font-weight:bold;"><strong>Монтажник</strong></td>
			<td style="font-weight:bold;"><strong>Вид работ</strong></td>
			<td style="font-weight:bold;"><strong>Тип работ</strong></td>
			<td style="font-weight:bold;"><strong>Наименование</strong></td>
			<td style="font-weight:bold;"><strong>Количество</strong></td>
		</tr>
		@foreach($rows as $row)
		<tr>
			<td>{{ $row->department }}</td>
			<td>{{ $row->town }}</td>
			<td>{{ $row->address }}</td>
			<!-- <td>-</td> -->
			<td>{{ $row->location }}</td>
			<td>{{ $row->work_type }}</td>
			<td>{{ $row->contract }}</td>
			<td>{{ $row->id_flow }}</td>
			<td>{{ $row->created_at }}</td>
			<td>{{ $row->installer }}</td>
			<td>{{ $row->type_flow }}</td>
			<td>{{ $row->type }}</td>
			<td>{{ $row->material_name }}</td>
			<td>{{ $row->qty }}</td>
		</tr>
		@endforeach
	</table>
</body>
</html>