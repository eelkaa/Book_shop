<?php 
$pageTitle = "О проекте";
include '../includes/header.php'; 
?>

<main style="padding: 2rem 0; min-height: calc(100vh - 180px); background: #f8fafc;">
    <div style="width: 95%; max-width: 1200px; margin: 0 auto;">
        <h1 style="font-size: 2rem; margin-bottom: 2rem; color: #1e293b; font-weight: 600;">О проекте BookHub</h1>
        
        <div style="background: white; padding: 2.5rem; border-radius: 12px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05); margin-bottom: 2rem;">
            <section style="margin-bottom: 3rem;">
                <h2 style="color: #7c3aed; margin-bottom: 1.5rem; font-size: 1.5rem; font-weight: 600;">📚 Наша миссия</h2>
                <p style="margin-bottom: 1.5rem; line-height: 1.7; color: #475569;">BookHub создан для тех, кто ценит хорошую литературу. Мы стремимся сделать чтение доступным и удобным, предлагая лучшие книги в цифровом формате.</p>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
                    <div style="background: #f5f3ff; padding: 1.5rem; border-radius: 8px; text-align: center;">
                        <div style="font-size: 2rem; color: #7c3aed; margin-bottom: 0.5rem;">10,000+</div>
                        <p style="color: #64748b;">Книг в каталоге</p>
                    </div>
                    <div style="background: #f5f3ff; padding: 1.5rem; border-radius: 8px; text-align: center;">
                        <div style="font-size: 2rem; color: #7c3aed; margin-bottom: 0.5rem;">24/7</div>
                        <p style="color: #64748b;">Доступ к библиотеке</p>
                    </div>
                    <div style="background: #f5f3ff; padding: 1.5rem; border-radius: 8px; text-align: center;">
                        <div style="font-size: 2rem; color: #7c3aed; margin-bottom: 0.5rem;">2015</div>
                        <p style="color: #64748b;">Год основания</p>
                    </div>
                </div>
            </section>

            <section style="margin-bottom: 3rem;">
                <h2 style="color: #7c3aed; margin-bottom: 1.5rem; font-size: 1.5rem; font-weight: 600;">👥 Наша команда</h2>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem;">
                    <div style="text-align: center; background: #f5f3ff; padding: 1.5rem; border-radius: 8px;">
                        <div style="width: 100px; height: 100px; background: #7c3aed; border-radius: 50%; margin: 0 auto 1rem; display: flex; align-items: center; justify-content: center; color: white; font-size: 2rem;">JD</div>
                        <h3 style="color: #7c3aed; margin-bottom: 0.5rem;">Матроскин</h3>
                        <p style="color: #64748b;">Основатель проекта</p>
                    </div>
                    <div style="text-align: center; background: #f5f3ff; padding: 1.5rem; border-radius: 8px;">
                        <div style="width: 100px; height: 100px; background: #7c3aed; border-radius: 50%; margin: 0 auto 1rem; display: flex; align-items: center; justify-content: center; color: white; font-size: 2rem;">AS</div>
                        <h3 style="color: #7c3aed; margin-bottom: 0.5rem;">Шерлок Холмс</h3>
                        <p style="color: #64748b;">Главный редактор</p>
                    </div>
                    <div style="text-align: center; background: #f5f3ff; padding: 1.5rem; border-radius: 8px;">
                        <div style="width: 100px; height: 100px; background: #7c3aed; border-radius: 50%; margin: 0 auto 1rem; display: flex; align-items: center; justify-content: center; color: white; font-size: 2rem;">MJ</div>
                        <h3 style="color: #7c3aed; margin-bottom: 0.5rem;">Карлсон</h3>
                        <p style="color: #64748b;">Технический директор</p>
                    </div>
                </div>
            </section>
        </div>

        <div style="background: #7c3aed; color: white; padding: 3rem; border-radius: 12px; text-align: center;">
            <h2 style="margin-bottom: 1.5rem; font-size: 1.5rem; font-weight: 600;">Присоединяйтесь к нашему книжному сообществу!</h2>
            <p style="margin-bottom: 2rem; max-width: 600px; margin-left: auto; margin-right: auto;">Подпишитесь на рассылку, чтобы получать новости о новых книгах и акциях.</p>
            <form style="max-width: 500px; margin: 0 auto; display: flex;">
                <input type="email" placeholder="Ваш email" style="
                    flex-grow: 1;
                    padding: 0.8rem;
                    border: none;
                    border-radius: 8px 0 0 8px;
                    font-size: 1rem;
                " required>
                <button type="submit" style="
                    background: white;
                    color: #7c3aed;
                    border: none;
                    padding: 0 1.5rem;
                    border-radius: 0 8px 8px 0;
                    font-weight: 600;
                    cursor: pointer;
                    transition: background 0.3s ease;
                ">Подписаться</button>
            </form>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>