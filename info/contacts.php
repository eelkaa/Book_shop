<?php 
$pageTitle = "Контакты";
include '../includes/header.php'; 
?>

<main style="padding: 2rem 0; min-height: calc(100vh - 180px); background: #f8fafc;">
    <div style="width: 95%; max-width: 1200px; margin: 0 auto;">
        <h1 style="font-size: 2rem; margin-bottom: 2rem; color: #1e293b; font-weight: 600;">Наши контакты</h1>
        
        <div style="background: white; padding: 2.5rem; border-radius: 12px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
                <div>
                    <h2 style="color: #7c3aed; margin-bottom: 1.5rem; font-size: 1.5rem; font-weight: 600;">
                        <i class="fas fa-envelope" style="margin-right: 0.5rem;"></i>Электронная почта
                    </h2>
                    <p style="margin-bottom: 1.5rem; color: #64748b;">info@bookhub.ru</p>
                    
                    <h2 style="color: #7c3aed; margin-bottom: 1.5rem; font-size: 1.5rem; font-weight: 600;">
                        <i class="fas fa-phone-alt" style="margin-right: 0.5rem;"></i>Телефон
                    </h2>
                    <p style="margin-bottom: 1.5rem; color: #64748b;">+7 (495) 123-45-67</p>
                </div>
                
                <div>
                    <h2 style="color: #7c3aed; margin-bottom: 1.5rem; font-size: 1.5rem; font-weight: 600;">
                        <i class="fas fa-map-marker-alt" style="margin-right: 0.5rem;"></i>Адрес
                    </h2>
                    <p style="margin-bottom: 1.5rem; color: #64748b;">г. Москва, ул. Книжная, д. 1</p>
                    
                    <h2 style="color: #7c3aed; margin-bottom: 1.5rem; font-size: 1.5rem; font-weight: 600;">
                        <i class="fas fa-clock" style="margin-right: 0.5rem;"></i>Режим работы
                    </h2>
                    <p style="color: #64748b;">Пн-Пт: 9:00 - 18:00</p>
                    <p style="color: #64748b;">Сб-Вс: выходной</p>
                </div>
            </div>
            
            <div style="margin-top: 3rem; background: #f5f3ff; padding: 2rem; border-radius: 8px;">
                <h2 style="color: #7c3aed; margin-bottom: 1.5rem; font-size: 1.5rem; font-weight: 600; text-align: center;">
                    Напишите нам
                </h2>
                <form style="max-width: 600px; margin: 0 auto;">
                    <div style="margin-bottom: 1rem;">
                        <input type="text" placeholder="Ваше имя" style="
                            width: 100%;
                            padding: 0.8rem;
                            border: 1px solid #e2e8f0;
                            border-radius: 8px;
                            font-size: 1rem;
                        " required>
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <input type="email" placeholder="Ваш email" style="
                            width: 100%;
                            padding: 0.8rem;
                            border: 1px solid #e2e8f0;
                            border-radius: 8px;
                            font-size: 1rem;
                        " required>
                    </div>
                    <div style="margin-bottom: 1.5rem;">
                        <textarea placeholder="Ваше сообщение" style="
                            width: 100%;
                            padding: 0.8rem;
                            border: 1px solid #e2e8f0;
                            border-radius: 8px;
                            font-size: 1rem;
                            min-height: 150px;
                        " required></textarea>
                    </div>
                    <button type="submit" style="
                        background: #7c3aed;
                        color: white;
                        border: none;
                        padding: 0.8rem 2rem;
                        border-radius: 8px;
                        font-weight: 600;
                        font-size: 1rem;
                        cursor: pointer;
                        transition: background 0.3s ease;
                        display: block;
                        margin: 0 auto;
                    ">Отправить сообщение</button>
                </form>
            </div>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>