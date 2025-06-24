<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Дашборд</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&icon_names=csv" />
    <link rel="stylesheet" href="css/style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="bg-gray-100">

<x-app-layout>
    @yield('content')
</x-app-layout>


<script>
    $(document).ready(function () {
        $('#uploadForm').on('submit', function (event) {
            event.preventDefault();
            var formData = new FormData(this);

            $.ajax({
                url: $(this).attr('action'),
                method: $(this).attr('method'),
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    $('#modalTitle').text('Успешно!');
                    $('#modalMessage').text(response.message + ' Обработано строк: ' + response.total_lines);
                    $('#resultModal').removeClass('hidden').addClass('flex');
                },
                error: function (xhr) {
                    let errorMessage = 'Произошла ошибка: ';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage += xhr.responseJSON.message;
                    } else {
                        errorMessage += 'Неизвестная ошибка.';
                    }

                    $('#modalTitle').text('Ошибка');
                    $('#modalMessage').text(errorMessage);
                    $('#resultModal').removeClass('hidden').addClass('flex');
                }
            });
        });

        $('#closeModalButton').on('click', function () {
            $('#resultModal').removeClass('flex').addClass('hidden');
        });
    });


</script>

@stack('scripts')
</body>
</html>
