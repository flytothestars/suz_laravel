<!DOCTYPE html>
<html lang="ru">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Сводный отчет ОС</title>
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
			<td style="font-weight:bold;"><strong>Приход ГПО</strong></td>
			<td style="font-weight:bold;"><strong>Демонтаж</strong></td>
			<td style="font-weight:bold;"><strong>Итого приход</strong></td>
			<td style="font-weight:bold;"><strong>Ремонт</strong></td>
			<td style="font-weight:bold;"><strong>Ремонт GPON</strong></td>
			<td style="font-weight:bold;"><strong>Ремонт ПД</strong></td>
			<td style="font-weight:bold;"><strong>Установка</strong></td>
			<td style="font-weight:bold;"><strong>Монтаж только аренда</strong></td>
			<td style="font-weight:bold;"><strong>Монтаж в собственность</strong></td>
			<td style="font-weight:bold;"><strong>Возврат демонтированного оборудования</strong></td>
			<td style="font-weight:bold;"><strong>Итого расход</strong></td>
			<td style="font-weight:bold;"><strong>Остаток на конец периода</strong></td>
		</tr>
		@foreach($rows as $row)
		<tr>
			<td>{{ $row->name }}</td>
			<td>{{ $row->balance_at_the_start }}</td>
			<td>{{ $row->upcoming }}</td>
			<td>{{ $row->dismantling }}</td>
			<td>{{ $row->upcoming_total }}</td>
			<td>{{ $row->repair }}</td>
			<td>{{ $row->repair_gpon }}</td>
			<td>{{ $row->repair_pd }}</td>
			<td>{{ $row->installs }}</td>
			<td>{{ $row->rent }}</td>
			<td>{{ $row->sold }}</td>
			<td>{{ $row->returns }}</td>
			<td>{{ $row->outgo_total }}</td>
			<td>{{ $row->balance_at_the_end }}</td>
		</tr>
		@endforeach
	</table>
</body>
</html>