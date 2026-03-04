<form action="" method="POST">
    <div class="form-group">
        <label>ФИО *</label>
        <input type="text" name="fullName" required>
    </div>
    
    <div class="form-group">
        <label>Email *</label>
        <input type="email" name="email" required>
    </div>
    
    <div class="form-group">
        <label>Телефон</label>
        <input type="tel" name="phone">
    </div>
    
    <div class="form-group">
        <label>Дата рождения *</label>
        <input type="date" name="birthdate" required>
    </div>
    
    <div class="form-group">
        <label>Пол *</label>
        <div class="radio-group">
            <input type="radio" name="gender" value="male" required> Мужской
            <input type="radio" name="gender" value="female" required> Женский
        </div>
    </div>
    
    <div class="form-group">
        <label>Любимые языки *</label>
        <select name="languages[]" multiple size="6" required>
            <option value="pascal">Pascal</option>
            <option value="c">C</option>
            <option value="cpp">C++</option>
            <option value="javascript">JavaScript</option>
            <option value="php">PHP</option>
            <option value="python">Python</option>
            <option value="java">Java</option>
            <option value="haskell">Haskell</option>
            <option value="clojure">Clojure</option>
            <option value="prolog">Prolog</option>
            <option value="scala">Scala</option>
            <option value="go">Go</option>
        </select>
        <small>Ctrl+клик для выбора нескольких</small>
    </div>
    
    <div class="form-group">
        <label>Биография *</label>
        <textarea name="message" required></textarea>
    </div>
    
    <div class="checkbox-group">
        <input type="checkbox" name="contract" required>
        <label>С контрактом ознакомлен(а) *</label>
    </div>
    
    <button type="submit" class="submit-btn">Сохранить</button>
</form>
