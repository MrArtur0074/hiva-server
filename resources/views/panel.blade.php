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
        <h1>Список доступных сайтов</h1>
        <select class="form-control" id="site-select">
            <option value="">Выберите сайт</option>
            @foreach ($sites as $site)
                <option value="{{ $site->id }}">{{ $site->url }}</option>
            @endforeach
        </select>

        <h2>Страницы сайта</h2>
        <table class="page-table">
            <thead>
                <tr>
                    <th>№</th>
                    <th>URL страницы</th>
                    <th>Выбрать</th>
                </tr>
            </thead>
            <tbody class="page-table-body">

            </tbody>
        </table>

        <div class="text-center mt-3">
            <button id="generate-button" class="btn btn-primary">Сгенерировать файл</button>
            <button id="select-all-button" class="btn btn-primary">Выделить все</button>
            <button id="deselect-all-button" class="btn btn-primary">Снять выделение</button>
        </div>
    </div>

    <!-- Модальное окно -->
    <div class="modal fade" id="resultModal" tabindex="-1" role="dialog" aria-labelledby="resultModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="resultModalLabel">Результаты создания вопросов и ответов</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Результаты создания вопросов и ответов доступны по следующему URL: <a href="/files-list">/files-list</a></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // Кнопка "Выделить все"
            $("#select-all-button").click(function() {
                $(".page-checkbox").prop("checked", true);
            });

            // Кнопка "Снять выделение"
            $("#deselect-all-button").click(function() {
                $(".page-checkbox").prop("checked", false);
            });

            // Обработчик кнопки "Сгенерировать файл"
            $("#generate-button").click(function() {
                let selectedValues = [];

                $(".page-checkbox:checked").each(function() {
                    selectedValues.push($(this).val());
                });

                $.ajax({
                    url: "/generate-file",
                    method: "POST",
                    data: { selectedValues: selectedValues },
                    success: function(response) {
                        console.log("Данные успешно отправлены.");
                    },
                    error: function() {
                        console.error("Произошла ошибка при отправке данных.");
                    }
                });

                // Открываем модальное окно с результатами
                $("#resultModal").modal("show");

                console.log(selectedValues);
            });

            // Обработчик изменения выбранного сайта (select)
            $("#site-select").change(function() {
                // Очищаем содержимое таблицы
                $(".page-table-body").empty();
                var siteId = $(this).val();

                // Если выбран сайт, выполните AJAX-запрос, чтобы получить его страницы
                if (siteId) {
                    $.ajax({
                        url: "/load-pages/" + siteId, // Замените на URL вашего маршрута для загрузки страниц
                        method: "GET",
                        success: function(data) {
                            console.log(data);
                            // Добавьте полученные страницы в выпадающий список
                            $.each(data.pages, function(index, page) {
                                $(".page-table-body").append('<tr><td>'+(index+1)+'</td><td>' + page.url + '</td><td><input name="url" type="checkbox" value="' + page.id + '" class="page-checkbox"></td></tr>');
                            });
                        },
                        error: function() {
                            console.error("Ошибка при загрузке страниц");
                        }
                    });
                }
            });
        });
    </script>
    </body>
</html>
