<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Laravel</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
        <!-- JavaScript и jQuery (необходимые для Bootstrap) -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

        <style>
            .page-table {
                width: 100%;
                border-collapse: collapse;
            }

            .page-table th,
            .page-table td {
                border: 1px solid #ddd;
                padding: 8px;
                text-align: left;
            }

            .page-table th {
                background-color: #f2f2f2;
            }
        </style>
    </head>
    <body>
        @include('blocks.header')
        <div class="container">
            <h1>Список файлов</h1>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Название файла</th>
                        <th>Статус</th>
                        <th>Ссылка на скачивание</th>
                        <th>Дата создания</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($files as $file)
                    <tr>
                        <td>{{ $file->id }}</td>
                        <td>{{ $file->filename }}</td>
                        <td>
                            @if ($file->status === 'Создается')
                                <span style="color: red;">{{ $file->status }}</span>
                            @elseif ($file->status === 'Завершен')
                                <span style="color: green;">{{ $file->status }}</span>
                            @endif
                            @if ($file->status === 'Создается')
                                (Ожидание ссылки)
                            @endif
                        </td>
                        <td>
                            @if ($file->status === 'Завершен')
                                <a href="{{ $file->download_link }}" target="_blank">Скачать</a>
                            @endif
                        </td>
                        <td>{{ $file->created_at }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </body>
</html>
