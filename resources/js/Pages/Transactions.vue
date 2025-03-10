<script setup>
import { ref, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';

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
    <div>
        <h2>记账记录</h2>
        <form @submit.prevent="addTransaction">
            <input v-model="newTransaction.date" type="date" required />
            <input v-model="newTransaction.amount" type="number" required />
            <select v-model="newTransaction.type" required>
                <option value="1">收入</option>
                <option value="2">支出</option>
            </select>
            <input v-model="newTransaction.category" placeholder="分类（可选）" />
            <input v-model="newTransaction.description" placeholder="描述（可选）" />
            <button type="submit">添加</button>
        </form>

        <ul>
            <li v-for="transaction in transactions" :key="transaction.id">
                {{ transaction.date }} - {{ transaction.amount }} - 
                {{ transaction.type == 1 ? '收入' : '支出' }}
            </li>
        </ul>
    </div>
</template>