<script setup>
import { ref, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

const transactions = ref([]);
const currentPage = ref(1);
const totalPages = ref(1);
const showModal = ref(false);
const newTransaction = ref({ date: '', description: '' });

const fetchTransactions = async (page = 1) => {
    const response = await axios.get(`/api/transactions?page=${page}`);
    transactions.value = response.data.data;
    totalPages.value = response.data.last_page;
    currentPage.value = response.data.current_page;
};

const addTransaction = async () => {
    await axios.post('/api/transactions', newTransaction.value);
    newTransaction.value = { date: '', description: '' };
    fetchTransactions(currentPage.value);
    showModal.value = false;
};

const deleteTransaction = async (id) => {
    await axios.delete(`/api/transactions/${id}`);
    fetchTransactions(currentPage.value);
};

const nextPage = () => {
    if (currentPage.value < totalPages.value) {
        fetchTransactions(currentPage.value + 1);
    }
};

const prevPage = () => {
    if (currentPage.value > 1) {
        fetchTransactions(currentPage.value - 1);
    }
};

onMounted(() => fetchTransactions(currentPage.value));
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <div class="container mx-auto p-4">
            <h2 class="text-2xl font-bold mb-4">记账记录</h2>
            <button @click="showModal = true" type="button" class="bg-green-500 text-white rounded p-2 mb-4">新增</button>

            <div class="grid grid-cols-4 gap-4">
                <div v-for="transaction in transactions" :key="transaction.id" class="border rounded-lg p-4 bg-white shadow relative">
                    <button @click="deleteTransaction(transaction.id)" class="absolute top-2 right-2 text-red-500 bg-white rounded-full p-1">❌</button>
                    <h3 class="text-lg font-bold">{{ transaction.date }}</h3>
                    <p>{{ transaction.description }}</p>
                </div>
            </div>

            <div class="flex justify-between mt-4">
                <button @click="prevPage" :disabled="currentPage === 1" class="bg-gray-500 text-white rounded p-2">上一页</button>
                <button @click="nextPage" :disabled="currentPage === totalPages" class="bg-gray-500 text-white rounded p-2">下一页</button>
            </div>
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