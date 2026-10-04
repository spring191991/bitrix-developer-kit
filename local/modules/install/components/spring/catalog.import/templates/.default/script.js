document.addEventListener('DOMContentLoaded', function() {
    
    const importForm = document.getElementById('spring-import-form');
    const messageBlock = document.getElementById('import-message');

    if (importForm) {
        
        importForm.addEventListener('submit', function(event) {
            event.preventDefault();

            messageBlock.innerHTML = '<p style="color: blue;">Файл загружается, пожалуйста, подождите...</p>';

            const formData = new FormData(importForm);

            fetch('/local/modules/spring.import/ajax.php', {
                method: 'POST',
                body: formData 
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Ошибка сервера: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                if (data.status === 'success') {
                    messageBlock.innerHTML = `<p style="color: green;">Успешно: ${data.message}</p>`;
                    importForm.reset();
                } else {
                    messageBlock.innerHTML = `<p style="color: red;">Ошибка: ${data.message}</p>`;
                }
            })
            .catch(error => {
                console.error('Критическая ошибка при отправке:', error);
                messageBlock.innerHTML = '<p style="color: red;">Не удалось связаться с сервером. Попробуйте позже.</p>';
            });

        });
    }
});
