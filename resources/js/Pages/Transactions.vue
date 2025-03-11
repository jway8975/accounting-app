<script setup>
import { ref, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

const transactions = ref([]);
const newTransaction = ref({ date: '', amount: '', type: '', category: '', description: '' });
const showModal = ref(false);

const fetchTransactions = async () => {
    const response = await axios.get('/api/transactions');
    transactions.value = response.data;
};

const addTransaction = async () => {
    await axios.post('/api/transactions', newTransaction.value);
    newTransaction.value = { date: '', amount: '', type: '', category: '', description: '' };
    fetchTransactions();
    showModal.value = false;
};

onMounted(fetchTransactions);
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <div class="container mx-auto p-4">
            <h2 class="text-2xl font-bold mb-4">记账记录</h2>
            <button @click="showModal = true" type="button" class="bg-green-500 text-white rounded p-2 mb-4">新增</button>

            <ul class="space-y-2">
                <li v-for="transaction in transactions" :key="transaction.id" class="border rounded p-4">
                    <div class="flex justify-between">
                        <span>{{ transaction.date }}</span>
                        <span>{{ transaction.description }}</span>
                    </div>
                </li>
            </ul>
        </div>

        <div v-if="showModal" class="fixed inset-0 flex items-center justify-center bg-gray-800 bg-opacity-75">
            <div class="bg-gradient-to-b from-sky-200 to-red-50 p-6 rounded-lg shadow-lg w-1/3">
                <h3 class="text-xl font-bold mb-4 text-black">新增记账记录</h3>
                <form @submit.prevent="addTransaction" class="space-y-4">
                    <div class="flex flex-col">
                        <label for="date" class="mb-1 text-black">日期</label>
                        <input id="date" v-model="newTransaction.date" type="date" required class="border rounded p-2 bg-white text-black" />
                    </div>
                    <div class="flex flex-col">
                        <label for="description" class="mb-1 text-black">描述（可选）</label>
                        <textarea id="description" v-model="newTransaction.description" placeholder="描述" class="border rounded p-2 h-32" />
                    </div>
                    <div class="flex justify-end space-x-2">
                        <button type="button" @click="showModal = false" class="bg-gray-500 text-white rounded p-2">关闭</button>
                        <button type="submit" class="bg-blue-500 text-white rounded p-2">添加</button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>