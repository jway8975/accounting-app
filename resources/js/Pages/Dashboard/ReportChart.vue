<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { Pie, Line } from 'vue-chartjs';
import { Chart as ChartJS, Title, Tooltip, Legend, ArcElement, CategoryScale, LinearScale, PointElement, LineElement } from 'chart.js';

ChartJS.register(Title, Tooltip, Legend, ArcElement, CategoryScale, LinearScale, PointElement, LineElement);

const trends = ref([]);
const categoryData = ref({});
const selectedMonth = ref('');
const selectedDate = ref('');

const fetchTrends = async () => {
    const response = await axios.get('/api/reports/trends');
    trends.value = response.data || [];

    if (trends.value.length > 0) {
        selectedMonth.value = trends.value[0].month;
        fetchDetails(selectedMonth.value);
    }
};

const fetchDetails = async (month) => {
    const response = await axios.get(`/api/reports/details/${month}`);
    categoryData.value = response.data.category_breakdown || {};
};

const handleMonthClick = (event, elements) => {
    if (elements.length > 0) {
        const index = elements[0].index;
        selectedMonth.value = trends.value[index].month;
        fetchDetails(selectedMonth.value);
    }
};

const handleDateChange = async () => {
    if (selectedDate.value) {
        const response = await axios.get(`/api/reports/details/${selectedDate.value}`);
        categoryData.value = response.data.category_breakdown || {};
    }
};

onMounted(fetchTrends);
</script>

<template>
    <div class="container mx-auto p-4">
        <h2 class="text-2xl font-bold mb-4">📊 财务报表（最近 12 个月）</h2>

        <div class="flex justify-between items-center mb-4">
            <div class="mb-4">
                <label for="date" class="block text-sm font-medium text-gray-700">选择日期</label>
                <input type="month" id="date" v-model="selectedDate" @change="handleDateChange" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
            </div>
            <div class="bg-white p-4 rounded-lg shadow-lg w-1/3">
                <h3 class="text-xl font-bold mb-4">📊 {{ selectedMonth }} 分类支出分析</h3>
                <Pie :data="{
                    labels: Object.keys(categoryData),
                    datasets: [{ data: Object.values(categoryData), backgroundColor: ['#ff6384', '#36a2eb', '#ffce56', '#4bc0c0', '#9966ff', '#ff9f40'] }]
                }" :options="{ plugins: { tooltip: { callbacks: { label: function(context) { return context.label + ': ' + context.raw + ' 元'; } } } } }" />
            </div>
        </div>

        <div class="bg-white p-4 rounded-lg shadow-lg mb-6">
            <Line
                :data="{
                    labels: trends.map(t => t.month),
                    datasets: [
                        { label: '收入', data: trends.map(t => t.income), borderColor: 'green', backgroundColor: 'rgba(0, 128, 0, 0.1)', pointRadius: 5, pointHoverRadius: 7 },
                        { label: '支出', data: trends.map(t => t.expense), borderColor: 'red', backgroundColor: 'rgba(255, 0, 0, 0.1)', pointRadius: 5, pointHoverRadius: 7 }
                    ]
                }"
                :options="{
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return value + ' 元';
                                }
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': ' + context.raw + ' 元';
                                }
                            }
                        }
                    },
                    onClick: handleMonthClick
                }"
            />
        </div>
    </div>
</template>