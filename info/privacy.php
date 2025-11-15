<?php 
$pageTitle = "Политика конфиденциальности";
include '../includes/header.php'; 
?>

<main style="padding: 2rem 0; min-height: calc(100vh - 180px); background: #f8fafc;">
    <div style="width: 95%; max-width: 1200px; margin: 0 auto;">
        <h1 style="font-size: 2rem; margin-bottom: 2rem; color: #1e293b; font-weight: 600;">Политика конфиденциальности</h1>
        
        <div style="background: white; padding: 2.5rem; border-radius: 12px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);">
            <p style="color: #64748b; margin-bottom: 2rem; font-style: italic;">Дата последнего обновления: <?= date('d.m.Y') ?></p>
            
            <section style="margin-bottom: 3rem;">
                <h2 style="color: #7c3aed; margin-bottom: 1.5rem; font-size: 1.5rem; font-weight: 600;">1. Какие данные мы собираем</h2>
                <p style="margin-bottom: 1.5rem; color: #475569;">Мы можем собирать следующую информацию:</p>
                <ul style="margin-left: 1.5rem; margin-bottom: 1.5rem; color: #475569;">
                    <li style="margin-bottom: 0.5rem; list-style-type: disc;">Персональные данные (имя, email, телефон)</li>
                    <li style="margin-bottom: 0.5rem; list-style-type: disc;">Данные для оплаты (обрабатываются платежными системами)</li>
                    <li style="margin-bottom: 0.5rem; list-style-type: disc;">Историю покупок и чтения</li>
                    <li style="list-style-type: disc;">Технические данные (IP-адрес, тип браузера)</li>
                </ul>
            </section>

            <section style="margin-bottom: 3rem;">
                <h2 style="color: #7c3aed; margin-bottom: 1.5rem; font-size: 1.5rem; font-weight: 600;">2. Как мы используем данные</h2>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 1.5rem;">
                    <div style="background: #f5f3ff; padding: 1.5rem; border-radius: 8px;">
                        <h3 style="color: #7c3aed; margin-bottom: 0.5rem; font-weight: 600;">🔒 Безопасность</h3>
                        <p style="color: #475569;">Для защиты вашего аккаунта и персональных данных</p>
                    </div>
                    <div style="background: #f5f3ff; padding: 1.5rem; border-radius: 8px;">
                        <h3 style="color: #7c3aed; margin-bottom: 0.5rem; font-weight: 600;">📦 Услуги</h3>
                        <p style="color: #475569;">Для предоставления доступа к купленным книгам</p>
                    </div>
                    <div style="background: #f5f3ff; padding: 1.5rem; border-radius: 8px;">
                        <h3 style="color: #7c3aed; margin-bottom: 0.5rem; font-weight: 600;">📊 Аналитика</h3>
                        <p style="color: #475569;">Для улучшения работы сервиса</p>
                    </div>
                </div>
            </section>

            <section>
                <h2 style="color: #7c3aed; margin-bottom: 1.5rem; font-size: 1.5rem; font-weight: 600;">3. Ваши права</h2>
                <p style="margin-bottom: 1rem; color: #475569;">Вы имеете право:</p>
                <ul style="margin-left: 1.5rem; margin-bottom: 1.5rem; color: #475569;">
                    <li style="margin-bottom: 0.5rem; list-style-type: disc;">Запросить доступ к вашим персональным данным</li>
                    <li style="margin-bottom: 0.5rem; list-style-type: disc;">Потребовать исправления неточных данных</li>
                    <li style="margin-bottom: 0.5rem; list-style-type: disc;">Отозвать согласие на обработку данных</li>
                    <li style="list-style-type: disc;">Удалить ваш аккаунт</li>
                </ul>
                <p style="color: #475569;">По всем вопросам обращайтесь на <a href="mailto:privacy@bookhub.ru" style="color: #7c3aed; font-weight: 500;">privacy@bookhub.ru</a></p>
            </section>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>