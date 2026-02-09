<script setup>
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Buyurtma qabul qilindi" />
    
    <div class="success-page">
        <div class="success-container">
            <div class="success-card">
                <div class="success-icon">
                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                        <polyline points="22,4 12,14.01 9,11.01"/>
                    </svg>
                </div>
                
                <h1 class="success-title">Buyurtma qabul qilindi!</h1>
                
                <p class="success-message">
                    Sizning buyurtmangiz muvaffaqiyatli ro'yxatdan o'tdi. 
                    Tez orada operator Siz bilan bog'lanadi.
                </p>
                
                <div class="success-actions">
                    <Link href="/" class="btn-primary">
                        Bosh sahifaga
                    </Link>
                    <Link href="/booking" class="btn-secondary">
                        Yana buyurtma berish
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.success-page {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
    padding: 24px;
}

.success-container {
    max-width: 500px;
    width: 100%;
}

.success-card {
    background: #fff;
    border-radius: 24px;
    padding: 48px 32px;
    text-align: center;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.success-icon {
    width: 100px;
    height: 100px;
    margin: 0 auto 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #dcfce7;
    border-radius: 50%;
    color: #22c55e;
}

.success-title {
    font-size: 28px;
    font-weight: 700;
    color: #1e293b;
    margin: 0 0 16px;
}

.success-message {
    font-size: 16px;
    color: #64748b;
    line-height: 1.6;
    margin: 0 0 32px;
}

.success-actions {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.btn-primary,
.btn-secondary {
    display: block;
    padding: 16px 24px;
    border-radius: 12px;
    font-size: 16px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.2s ease;
}

.btn-primary {
    background: #3b82f6;
    color: #fff;
}

.btn-primary:hover {
    background: #2563eb;
}

.btn-secondary {
    background: #f1f5f9;
    color: #475569;
}

.btn-secondary:hover {
    background: #e2e8f0;
}
</style>
