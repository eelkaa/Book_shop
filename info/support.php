<?php 
$pageTitle = "Поддержка";
include '../includes/header.php'; 
?>

<main style="padding: 2rem 0; min-height: calc(100vh - 200px);">
    <div class="container" style="width: 90%; max-width: 1200px; margin: 0 auto;">
        <h1 style="margin-bottom: 1.5rem; color: #2c3e50;">Служба поддержки</h1>
        
        <div style="background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);">
            <section style="margin-bottom: 2rem;">
                <h2 style="color: #2c3e50; margin-bottom: 1rem; font-size: 1.5rem;">📨 Свяжитесь с нами</h2>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
                    <div style="background: #f9f9f9; padding: 1.5rem; border-radius: 8px;">
                        <h3 style="color: #f39c12; margin-bottom: 1rem;">✉️ Email</h3>
                        <p style="margin-bottom: 1rem;">Основной канал связи:</p>
                        <p><a href="mailto:support@bookzone.ru" style="color: #2c3e50; font-weight: 600;">support@bookzone.ru</a></p>
                        <p>Среднее время ответа: 24 часа</p>
                    </div>
                    
                    
                </div>
            </section>

            <section>
                <h2 style="color: #2c3e50; margin-bottom: 1rem; font-size: 1.5rem;">📋 Форма обратной связи</h2>
                <form style="max-width: 600px; margin-top: 1.5rem;">
                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Ваше имя</label>
                        <input type="text" style="
                            width: 100%;
                            padding: 0.8rem;
                            border: 1px solid #ddd;
                            border-radius: 5px;
                            font-size: 1rem;
                        " required>
                    </div>
                    
                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Email</label>
                        <input type="email" style="
                            width: 100%;
                            padding: 0.8rem;
                            border: 1px solid #ddd;
                            border-radius: 5px;
                            font-size: 1rem;
                        " required>
                    </div>
                    
                    <div style="margin-bottom: 1.5rem;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Сообщение</label>
                        <textarea style="
                            width: 100%;
                            padding: 0.8rem;
                            border: 1px solid #ddd;
                            border-radius: 5px;
                            font-size: 1rem;
                            min-height: 150px;
                        " required></textarea>
                    </div>
                    
                    <button type="submit" style="
                        background: #2c3e50;
                        color: white;
                        border: none;
                        padding: 0.9rem 2rem;
                        border-radius: 5px;
                        font-weight: 600;
                        cursor: pointer;
                        font-size: 1rem;
                        transition: background 0.3s;
                    ">Отправить запрос</button>
                </form>
            </section>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>