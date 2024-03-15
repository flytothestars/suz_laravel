<!DOCTYPE html>
<html lang="ru">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Отчет по заявкам</title>
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
        <td>Номер наряда</td>
        <td>Тип заказа</td>
        <td>Дата создания заказа</td>
        <td>Дата завершения заказа</td>
        <td>Номер контракта</td>
        <td>Филиал</td>
        <td>Наименование участка</td>
        <td>Адрес клиента</td>
        <td>Статус заказа</td>
        <td>Номер сектора</td>
        <td>Категория контракта</td>
        <td>Тип работы</td>
        <td>Вид работ</td>
        <td>Технология услуг</td>
        <td>Классификация ремонта</td>
        <td>Примечание</td>
        <td>Принятые меры (комментарий техника)</td>
        <td>Плановая дата</td>
        <td>Завершивший заказ</td>
        <td>Техник 1</td>
        <td>Техник 2</td>
    </tr>
    @foreach($requests as $request)
        <tr>
            <td>{{ $request->id_flow }}</td>
            <td>{{ $request->ci_flow }}</td>
            <td>{{ $request->dt_start }}</td>
            <td>{{ $request->dt_stop }}</td>
            <td>{{ $request->contract }}</td>
            <td>{{ $request->department }}</td>
            <td>{{ $request->location }}</td>
            <td>{{ $request->address }}</td>
            <td>{{ $request->status }}</td>
            <td>{{ $request->sector }}</td>
            <td>{{ $request->product }}</td>
            <td>{{ $request->kind_works }}</td>
            <td>{{ $request->ltypework}}</td>
            <td>{{ $request->service_name . ':' .  $request->technology_name }}</td>
            <td>
                {{ $request->repair_types  }}
            </td>
            <td>{{ $request->description }}</td>
            <td>{{ $request->comment }}</td>
            <td>{{ $request->dt_plan_date }}</td>
            <td>{{ $request->completing ?: '' }}</td>
            <td>{{ $request->installer1 }}</td>
            <td>{{ $request->installer2 }}</td>
        </tr>
    @endforeach
</table>
</body>
</html>
