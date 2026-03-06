const openModalBtn = document.getElementById('openModalBtn');
const closeModalBtn = document.getElementById('closeModalBtn');
const feedbackModal = document.getElementById('feedbackModal');
const feedbackForm = document.getElementById('feedbackForm');
const responseMessage = document.getElementById('responseMessage');

const STORAGE_KEY = 'feedbackFormData';

// Валидация ФИО (только буквы, пробелы и дефисы)
function validateFullName(name) {
    return /^[a-zA-Zа-яА-ЯёЁ\s\-]+$/.test(name);
}

// Валидация телефона (только цифры, пробелы и плюс, ровно 10 цифр)
function validatePhone(phone) {
    // Убираем все пробелы и плюсы для подсчета цифр
    const digitsOnly = phone.replace(/[\s\+]/g, '');
    // Проверяем, что есть ровно 10 цифр и нет других символов
    return /^[\d\s\+]+$/.test(phone) && digitsOnly.length === 10;
}

// Проверка выбора пола
function validateGender() {
    const genderSelected = document.querySelector('input[name="gender"]:checked');
    const genderError = document.querySelector('.gender-error');
    const genderGroup = document.getElementById('gender-group');

    if (!genderSelected) {
        genderError.style.display = 'block';
        genderGroup.classList.add('error-border');
        return false;
    } else {
        genderError.style.display = 'none';
        genderGroup.classList.remove('error-border');
        return true;
    }
}

// Проверка выбора языков программирования
function validateLanguages() {
    const languagesSelect = document.getElementById('languages');
    const selectedOptions = Array.from(languagesSelect.selectedOptions);
    const languagesError = document.getElementById('languages-error') || createLanguagesError();

    if (selectedOptions.length === 0) {
        languagesError.style.display = 'block';
        languagesSelect.style.borderColor = '#dc3545';
        return false;
    } else {
        languagesError.style.display = 'none';
        languagesSelect.style.borderColor = '#C2C5CE';
        return true;
    }
}

// Создание элемента для ошибки языков (если его нет)
function createLanguagesError() {
    const languagesGroup = document.querySelector('.form-group:has(#languages)');
    const errorElement = document.createElement('div');
    errorElement.id = 'languages-error';
    errorElement.className = 'field-error';
    errorElement.textContent = 'Пожалуйста, выберите хотя бы один язык программирования';
    errorElement.style.display = 'none';
    languagesGroup.appendChild(errorElement);
    return errorElement;
}

// Открытие модального окна
openModalBtn.addEventListener('click', function() {
    feedbackModal.style.display = 'flex';
    // Изменение URL с помощью History API
    history.pushState({ modalOpen: true }, '', '#feedback');
    // Восстановление данных из LocalStorage
    restoreFormData();
    // Скрываем ошибки при открытии
    document.querySelector('.gender-error').style.display = 'none';

    // Создаем элемент ошибки для языков, если его нет
    if (!document.getElementById('languages-error')) {
        createLanguagesError();
    } else {
        document.getElementById('languages-error').style.display = 'none';
    }
});

// Закрытие модального окна
closeModalBtn.addEventListener('click', closeModal);

// Закрытие модального окна при клике вне его области
feedbackModal.addEventListener('click', function(e) {
    if (e.target === feedbackModal) {
        closeModal();
    }
});

// Обработка нажатия кнопки "Назад" в браузере
window.addEventListener('popstate', function(e) {
    if (location.hash !== '#feedback') {
        closeModal();
    }
});

// Функция закрытия модального окна
function closeModal() {
    feedbackModal.style.display = 'none';
    // Возврат к исходному URL
    if (location.hash === '#feedback') {
        history.back();
    }
}

// Сохранение данных формы в LocalStorage
function saveFormData() {
    // Получаем выбранные языки
    const languagesSelect = document.getElementById('languages');
    const selectedLanguages = Array.from(languagesSelect.selectedOptions).map(opt => opt.value);

    const formData = {
        fullName: document.getElementById('fullName').value,
        email: document.getElementById('email').value,
        phone: document.getElementById('phone').value,
        birthdate: document.getElementById('birthdate').value,
        gender: document.querySelector('input[name="gender"]:checked')?.value || '',
        languages: selectedLanguages,
        message: document.getElementById('message').value,
        contract: document.getElementById('contract').checked
    };
    localStorage.setItem(STORAGE_KEY, JSON.stringify(formData));
}

// Восстановление данных формы из LocalStorage
function restoreFormData() {
    const savedData = localStorage.getItem(STORAGE_KEY);
    if (savedData) {
        const formData = JSON.parse(savedData);
        document.getElementById('fullName').value = formData.fullName || '';
        document.getElementById('email').value = formData.email || '';
        document.getElementById('phone').value = formData.phone || '';
        document.getElementById('birthdate').value = formData.birthdate || '';

        if (formData.gender) {
            document.querySelector(`input[name="gender"][value="${formData.gender}"]`).checked = true;
        }

        // Восстанавливаем выбранные языки
        if (formData.languages && formData.languages.length) {
            const languagesSelect = document.getElementById('languages');
            Array.from(languagesSelect.options).forEach(opt => {
                opt.selected = formData.languages.includes(opt.value);
            });
        }

        document.getElementById('message').value = formData.message || '';
        document.getElementById('contract').checked = formData.contract || false;
    }
}

// Очистка данных формы в LocalStorage
function clearFormData() {
    localStorage.removeItem(STORAGE_KEY);
}

// Валидация формы перед отправкой
function validateForm() {
    const fullName = document.getElementById('fullName').value;
    const phone = document.getElementById('phone').value;
    const birthdate = document.getElementById('birthdate').value;

    let isValid = true;

    // Валидация ФИО
    if (fullName && !validateFullName(fullName)) {
        showFieldError('fullName', 'ФИО может содержать только буквы, пробелы и дефисы');
        isValid = false;
    } else {
        clearFieldError('fullName');
    }

    // Валидация телефона
    if (phone) {
        const digitsOnly = phone.replace(/[\s\+]/g, '');
        if (!validatePhone(phone)) {
            if (digitsOnly.length !== 10) {
                showFieldError('phone', 'Телефон должен содержать ровно 10 цифр');
            } else {
                showFieldError('phone', 'Телефон может содержать только цифры, пробелы и +');
            }
            isValid = false;
        } else {
            clearFieldError('phone');
        }
    }

    // Проверка даты рождения
    if (!birthdate) {
        showFieldError('birthdate', 'Пожалуйста, укажите дату рождения');
        isValid = false;
    } else {
        clearFieldError('birthdate');
    }

    // Проверка выбора пола
    if (!validateGender()) {
        isValid = false;
    }

    // Проверка выбора языков
    if (!validateLanguages()) {
        isValid = false;
    }

    return isValid;
}

// Показать ошибку для конкретного поля
function showFieldError(fieldId, message) {
    const field = document.getElementById(fieldId);
    const formGroup = field.closest('.form-group');

    // Удаляем старую ошибку если есть
    const existingError = formGroup.querySelector('.field-error:not(.gender-error):not(#languages-error)');
    if (existingError) {
        existingError.remove();
    }

    // Добавляем новую ошибку
    const errorElement = document.createElement('div');
    errorElement.className = 'field-error';
    errorElement.textContent = message;

    formGroup.appendChild(errorElement);
    field.style.borderColor = '#dc3545';
}

// Очистить ошибку поля
function clearFieldError(fieldId) {
    const field = document.getElementById(fieldId);
    const formGroup = field.closest('.form-group');
    const existingError = formGroup.querySelector('.field-error:not(.gender-error):not(#languages-error)');

    if (existingError) {
        existingError.remove();
    }

    field.style.borderColor = '#C2C5CE';
}

// Обработка отправки формы
feedbackForm.addEventListener('submit', function(e) {
    e.preventDefault();

    // Проверяем валидацию перед отправкой
    if (!validateForm()) {
        showMessage('Пожалуйста, исправьте ошибки в форме', 'error');
        return;
    }

    // Сбор данных формы
    const formData = new FormData(feedbackForm);

    // Удаляем старый массив languages (если есть)
    formData.delete('languages');

    // Добавляем каждый выбранный язык отдельно как массив
    const languagesSelect = document.getElementById('languages');
    const selectedLanguages = Array.from(languagesSelect.selectedOptions).map(opt => opt.value);
    selectedLanguages.forEach(lang => {
        formData.append('languages[]', lang);
    });

    // Добавляем чекбокс контракта (если отмечен)
    if (document.getElementById('contract').checked) {
        formData.set('contract', 'on');
    } else {
        formData.delete('contract');
    }

    // Отправляем данные
    fetch('index.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (response.redirected) {
            // Успешная отправка (редирект на ?save=1)
            showMessage('Данные успешно сохранены!', 'success');
            feedbackForm.reset();
            clearFormData();

            // Скрываем ошибки
            document.querySelector('.gender-error').style.display = 'none';
            if (document.getElementById('languages-error')) {
                document.getElementById('languages-error').style.display = 'none';
            }

            // Через 2 секунды перезагружаем страницу, чтобы увидеть сообщение
            setTimeout(() => {
                window.location.href = response.url;
            }, 2000);

        } else {
            // Возможно, вернулась страница с ошибками
            return response.text().then(html => {
                // Проверяем, содержит ли ответ сообщение об ошибке
                if (html.includes('Заполните') || html.includes('Некорректный') ||
                    html.includes('Выберите') || html.includes('Подтвердите') ||
                    html.includes('Ошибка')) {

                    // Извлекаем сообщение об ошибке
                    const errorMatch = html.match(/(Заполните[^<]+|Некорректный[^<]+|Выберите[^<]+|Подтвердите[^<]+|Ошибка[^<]+)/);
                    if (errorMatch) {
                        throw new Error(errorMatch[0]);
                    } else {
                        throw new Error('Ошибка при отправке формы');
                    }
                } else {
                    throw new Error('Неизвестная ошибка');
                }
            });
        }
    })
    .catch(error => {
        console.error('Ошибка:', error);
        showMessage(error.message, 'error');
    });
});

// Функция отображения сообщения
function showMessage(text, type) {
    responseMessage.textContent = text;
    responseMessage.className = 'message ' + type;
    responseMessage.style.display = 'block';

    // Автоматическое скрытие сообщения через 5 секунд
    setTimeout(() => {
        responseMessage.style.display = 'none';
    }, 5000);
}

// Обработчики для реальной валидации при вводе и сохранения
document.getElementById('fullName').addEventListener('input', function(e) {
    if (this.value && !validateFullName(this.value)) {
        showFieldError('fullName', 'ФИО может содержать только буквы, пробелы и дефисы');
    } else {
        clearFieldError('fullName');
    }
    saveFormData();
});

document.getElementById('phone').addEventListener('input', function(e) {
    const digitsOnly = this.value.replace(/[\s\+]/g, '');

    if (this.value && !validatePhone(this.value)) {
        if (digitsOnly.length !== 10) {
            showFieldError('phone', 'Телефон должен содержать ровно 10 цифр');
        } else {
            showFieldError('phone', 'Телефон может содержать только цифры, пробелы и +');
        }
    } else {
        clearFieldError('phone');
    }
    saveFormData();
});

document.getElementById('birthdate').addEventListener('change', function(e) {
    if (!this.value) {
        showFieldError('birthdate', 'Пожалуйста, укажите дату рождения');
    } else {
        clearFieldError('birthdate');
    }
    saveFormData();
});

// Обработчик для выбора языков
document.getElementById('languages').addEventListener('change', function() {
    validateLanguages();
    saveFormData();
});

// Обработчики для радио-кнопок пола
document.querySelectorAll('input[name="gender"]').forEach(radio => {
    radio.addEventListener('change', function() {
        validateGender();
        saveFormData();
    });
});

// Сохранение данных формы при изменении всех полей
const allInputs = feedbackForm.querySelectorAll('input, textarea, select');
allInputs.forEach(input => {
    input.addEventListener('input', saveFormData);
    input.addEventListener('change', saveFormData); // Для radio, checkbox, select
});
