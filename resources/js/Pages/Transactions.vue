<script setup>
import { ref, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

const transactions = ref([]);
const newTransaction = ref({ date: '', amount: '', type: '', category: '', description: '' });

const fetchTransactions = async () => {
    const response = await axios.get('/api/transactions');
    transactions.value = response.data;
};

const addTransaction = async () => {
    await axios.post('/api/transactions', newTransaction.value);
    newTransaction.value = { date: '', amount: '', type: '', category: '', description: '' };
    fetchTransactions();
};

onMounted(fetchTransactions);
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <div class="container mx-auto p-4">
            <h2 class="text-2xl font-bold mb-4">记账记录</h2>
            <form @submit.prevent="addTransaction" class="space-y-4 mb-6">
                <div class="flex flex-col">
                    <label for="date" class="mb-1">日期</label>
                    <input id="date" v-model="newTransaction.date" type="date" required class="border rounded p-2" />
                </div>
                <div class="flex flex-col">
                    <label for="amount" class="mb-1">金额</label>
                    <input id="amount" v-model="newTransaction.amount" type="number" required class="border rounded p-2" />
                </div>
                <div class="flex flex-col">
                    <label for="type" class="mb-1">类型</label>
                    <select id="type" v-model="newTransaction.type" required class="border rounded p-2">
                        <option value="1">收入</option>
                        <option value="2">支出</option>
                    </select>
                </div>
                <div class="flex flex-col">
                    <label for="category" class="mb-1">分类（可选）</label>
                    <input id="category" v-model="newTransaction.category" placeholder="分类（可选）" class="border rounded p-2" />
                </div>
                <div class="flex flex-col">
                    <label for="description" class="mb-1">描述（可选）</label>
                    <input id="description" v-model="newTransaction.description" placeholder="描述（可选）" class="border rounded p-2" />
                </div>
                <button type="submit" class="bg-blue-500 text-white rounded p-2">添加</button>
            </form>

            <button type="button" class="bg-green-500 text-white rounded p-2">新增</button>

            <ul class="space-y-2">
                <li v-for="transaction in transactions" :key="transaction.id" class="border rounded p-4">
                    <div class="flex justify-between">
                        <span>{{ transaction.date }}</span>
                        <span>{{ transaction.amount }}</span>
                        <span>{{ transaction.type == 1 ? '收入' : '支出' }}</span>
                    </div>
                </li>
            </ul>
        </div>
    </AuthenticatedLayout>
</template>