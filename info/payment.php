<?php 
$pageTitle = "Способы оплаты";
include '../includes/header.php'; 
?>

<main style="padding: 2rem 0; min-height: calc(100vh - 180px); background: #f8fafc;">
    <div style="width: 95%; max-width: 1200px; margin: 0 auto;">
        <h1 style="font-size: 2rem; margin-bottom: 2rem; color: #1e293b; font-weight: 600;">Способы оплаты</h1>
        
        <div style="background: white; padding: 2.5rem; border-radius: 12px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);">
            <section style="margin-bottom: 3rem;">
                <h2 style="color: #7c3aed; margin-bottom: 1.5rem; font-size: 1.5rem; font-weight: 600;">
                    <i class="fas fa-credit-card" style="margin-right: 0.5rem;"></i>Банковские карты
                </h2>
                <p style="margin-bottom: 1.5rem; color: #475569; line-height: 1.7;">Мы принимаем все основные платежные системы:</p>
                <ul style="margin-left: 1.5rem; margin-bottom: 1.5rem; color: #475569;">
                    <li style="margin-bottom: 0.5rem; list-style-type: disc;">Visa, Mastercard, Мир</li>
                    <li style="margin-bottom: 0.5rem; list-style-type: disc;">Безопасные платежи через CloudPayments</li>
                    <li style="list-style-type: disc;">3D-Secure защита</li>
                </ul>
            </section>

            <section style="margin-bottom: 3rem;">
                <h2 style="color: #7c3aed; margin-bottom: 1.5rem; font-size: 1.5rem; font-weight: 600;">
                    <i class="fas fa-wallet" style="margin-right: 0.5rem;"></i>Электронные кошельки
                </h2>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1.5rem; margin-bottom: 1.5rem;">
                    <div style="background: #f5f3ff; padding: 1.5rem; border-radius: 8px; text-align: center;">
                        <div style="font-size: 2rem; color: #7c3aed; margin-bottom: 0.5rem;">🤖</div>
                        <p style="color: #475569; font-weight: 500;">QIWI</p>
                    </div>
                    <div style="background: #f5f3ff; padding: 1.5rem; border-radius: 8px; text-align: center;">
                        <div style="font-size: 2rem; color: #7c3aed; margin-bottom: 0.5rem;">💵</div>
                        <p style="color: #475569; font-weight: 500;">ЮMoney</p>
                    </div>
                    <div style="background: #f5f3ff; padding: 1.5rem; border-radius: 8px; text-align: center;">
                        <div style="font-size: 2rem; color: #7c3aed; margin-bottom: 0.5rem;">🅿️</div>
                        <p style="color: #475569; font-weight: 500;">PayPal</p>
                    </div>
                </div>
            </section>

            <section>
                <h2 style="color: #7c3aed; margin-bottom: 1.5rem; font-size: 1.5rem; font-weight: 600;">
                    <i class="fas fa-question-circle" style="margin-right: 0.5rem;"></i>Частые вопросы
                </h2>
                <div style="margin-bottom: 2rem; background: #f5f3ff; padding: 1.5rem; border-radius: 8px;">
                    <h3 style="color: #7c3aed; margin-bottom: 0.5rem; font-weight: 600;">Как вернуть деньги?</h3>
                    <p style="color: #475569;">Возврат осуществляется в течение 14 дней с момента покупки. Для инициации возврата напишите в <a href="../info/support.php" style="color: #7c3aed; font-weight: 500;">поддержку</a>.</p>
                </div>
                <div style="background: #f5f3ff; padding: 1.5rem; border-radius: 8px;">
                    <h3 style="color: #7c3aed; margin-bottom: 0.5rem; font-weight: 600;">Безопасны ли платежи?</h3>
                    <p style="color: #475569;">Все транзакции защищены 256-битным SSL-шифрованием. Мы не храним данные вашей карты.</p>
                </div>
            </section>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>