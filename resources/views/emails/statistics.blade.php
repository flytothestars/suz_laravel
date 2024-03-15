<table style="border-collapse: collapse; width: 100%;">
    <thead style="background-color: #f2f2f2;">
    <tr>
        <th style="border: 1px solid #ddd; padding: 8px;">№Заказа в СУЗ</th>
        <th style="border: 1px solid #ddd; padding: 8px;">ФИО Исполнителя</th>
        <th style="border: 1px solid #ddd; padding: 8px;">Превышенный ТМЦ (наименования через запятую)</th>
        <th style="border: 1px solid #ddd; padding: 8px;">Дата/Время события</th>
    </tr>
    </thead>
    <tbody>
    @foreach ($statisticData as $requestId => $data)
        @foreach($data as $key => $lim)
            @php
                $dates[] = isset($lim[0]['limits']) ? $lim[0]['limits']->created_at->format('d.m.Y H:i:s') : null;
                $fios[] = isset($lim[0]['limits']) ? $lim[0]['limits']->installer->name : null;
            @endphp
        @endforeach

        @php
            $fios = array_filter($fios);
            $fios = array_unique($fios);
            $dates = array_filter($dates);
            $dates = array_unique($dates);
        @endphp

        <tr>
            <td style="border: 1px solid #ddd; padding: 8px;">{{ $requestId }}</td>
            <td style="border: 1px solid #ddd; padding: 8px;">{{ join(',',$fios)}}</td>
            <td style="border: 1px solid #ddd; padding: 8px;">{{ $data['titles'] }}</td>
            <td style="border: 1px solid #ddd; padding: 8px;">{{ join(',',$dates) }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
