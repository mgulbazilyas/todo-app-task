<script setup>
import Modal from '@/Components/Modal.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, onBeforeUnmount, reactive } from 'vue';
import axios from 'axios'

const apiMeta = ref({

});
const loading = ref(false);
const pageData = reactive({
    todo: null,
    modal: {
        show: false,
        todo: null,
        todoIndex: null,
    },
    items: [],
    pageNo: 1,
    loading: false,
    searchTerm: "",
})
const search = () => {
    pageData.pageNo = 1;
    pageData.items = [];
    getPageData();
}
const getPageData = () => {
    loading.value = true;
    fetch(`/api/todos?page=${pageData.pageNo}&search=${pageData.searchTerm}`).then((response) => response.json().then((response) => {
        console.log(response);
        const data = response.data;
        pageData.items = pageData.items.concat(data.data);
        delete data.items;
        apiMeta.value = data;
        loading.value = false;

    }));
}
const infiniteScrollTrigger = ref();
// Add scroll event listener
const handleScroll = () => {
    const trigger = infiniteScrollTrigger.value;
    if (trigger) {
        // debugger;
        const rect = trigger.getBoundingClientRect();

        const isInView = (
            rect.top >= 0 &&
            rect.bottom <= (window.innerHeight || document.documentElement.clientHeight)
        );

        if (isInView) {
            loadMoreItems();
        } else {
            console.log("not in view");
        }

    } else {
        console.log("trigger not visible");
    }
};

onMounted(() => {
    window.addEventListener('scroll', handleScroll);
    getPageData();
});

onBeforeUnmount(() => {
    window.removeEventListener('scroll', handleScroll);
});

const loadMoreItems = () => {
    // Call your API or method to load more items
    // Update items.value with the new data
    if (apiMeta.value.next_page_url) {
        pageData.pageNo += 1;
        getPageData();
        console.log("LoadMoreItems");

    } else {

    }
};
const statusToText = {
    0: "Pending",
    1: "In Progress",
    2: "Completed",
}
const getStatusColorClass = (status) => {
    switch (status) {
        case 0:
            return "text-red-500"; // Tailwind CSS class for red text
        case 1:
            return "text-blue-500"; // Tailwind CSS class for blue text
        case 2:
            return "text-green-500"; // Tailwind CSS class for green text
        default:
            return "text-black"; // Default color if status is not recognized
    }
}
const createNewTodo = (item) => {
    pageData.modal.todo = {};
    pageData.modal.show = true;
}
const editTodo = (item) => {
    pageData.modal.todo = { ...item };
    pageData.modal.show = true;
}
const deleteTodo = (item) => {
    const isConfirm = confirm("Are you sure you want to delete");
    if (isConfirm) {
        let config = {
            method: 'delete',
            maxBodyLength: Infinity,
            url: `/api/todos/${item.id}`,
        };
        pageData.loading = true;
        axios.request(config)
            .then((response) => {
                console.log(JSON.stringify(response.data));
                const index = pageData.items.findIndex(itm => itm.id === item.id);
                pageData.items.splice(index, 1);
                alert("TODO Deleted");
            })
            .catch((error) => {
                console.log(error);
            }).finally(() => {
                pageData.loading = false;
            });
    } else {

    }

}
const handleSubmit = () => {
    let todo = pageData.modal.todo;

    if (todo.id) {
        // Means this is update request
        const id = todo.id;
        delete todo.id;
        const raw = JSON.stringify({ ...todo, });
        todo.id = id;
        const config = {
            method: 'put',
            url: `/api/todos/${id}`,
            data: raw
        };
        pageData.loading = true;
        axios(config)
            .then(function (response) {
                const index = pageData.items.findIndex(item => item.id === id);
                pageData.items[index] = { ...todo };

            })
            .catch(function (error) {
                console.log(error);
            }).finally(() => {
                pageData.loading = false;
                alert("todo editted successfully");
                pageData.modal.show = false;
            });
    } else {
        // Create New Item
        const raw = JSON.stringify({ ...todo, });
        const config = {
            method: 'post',
            url: `/api/todos`,
            data: raw
        };
        pageData.loading = true;
        axios(config)
            .then(function (response) {
                pageData.items = [];
                pageData.pageNo = 1;
                getPageData();
            })
            .catch(function (error) {
                console.log(error);
            }).finally(() => {
                pageData.loading = false;
                alert("TODO Created successfully");
                pageData.modal.show = false;
            });

    }
}
import { usePage } from '@inertiajs/vue3'
const page = usePage();
console.log(page.props);

</script>


<style scoped>
input {
    border: 1px solid #ccc;
    border-radius: 0;
}

button {
    border-radius: 0;
}

.infinite-scroll-trigger {
    height: 50px;
    /* Adjust this height as needed */
    background-color: #f5f5f5;
    /* Adjust the color as needed */
}

/* Add this CSS in your styles file or in your HTML file if you're not using an external stylesheet */
@keyframes spin {
    0% {
        transform: rotate(0deg);
    }

    100% {
        transform: rotate(360deg);
    }
}

.loader {
    border: 4px solid rgba(255, 255, 255, 0.3);
    border-top: 4px solid #000;
    border-radius: 50%;
    width: 36px;
    height: 36px;
    animation: spin 1s linear infinite;

}
</style>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>

        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">TODO</h2>
        </template>

        <div class="row p-12">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center">
                    <input v-model="pageData.searchTerm" type="text"
                        class="border rounded-l py-2 px-3 focus:outline-none focus:ring focus:border-blue-300"
                        placeholder="Search..." />
                    <button @click="search" class="bg-blue-500 text-white py-2 px-4 rounded-r">Search</button>
                </div>
                <button @click="createNewTodo" class="bg-green-500 text-white py-2 px-4 rounded">New Todo</button>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                <!-- Todo Card 1 -->
                <div class="bg-white p-4 shadow-md rounded-lg" v-for="item in pageData.items">
                    <h3 class="text-xl font-bold mb-2">{{ item.title }}</h3>
                    <p class="text-gray-700 mb-4">{{ item.description }}</p>
                    <p class="mb-2">
                        Status:
                        <span class="font-semibold" :class="getStatusColorClass(item.status)">
                            {{ statusToText[item.status] }}
                        </span>
                    </p>

                        <button class="bg-blue-500 text-white px-4 py-2 rounded-full mr-2" :disabled="pageData.loading"
                            @click="editTodo(item)">Edit</button>
                        <button class="bg-red-500 text-white px-4 py-2 rounded-full" :disabled="pageData.loading"
                            @click="deleteTodo(item)">Delete</button>


                </div>


            </div>

        </div>
        <!-- Loading Row -->
        <div class="flex justify-center items-center mt-8 mb-8">
            <h2 v-if="apiMeta.current_page && (!apiMeta.next_page_url)">No More Items</h2>
            <div class="infinite-scroll-trigger" ref="infiniteScrollTrigger" v-else-if="!loading"></div>
            <div class="loader" v-else></div>
        </div>
        <Modal :show="pageData.modal.show">
            <div class="row">

                <div class="flex justify-between items-center mb-4 mt-2 mr-2">
                    <h1 class="pl-2" v-if="pageData.modal.todo.id">Update TODO</h1>
                    <h1 class="pl-2" v-else>Create TODO</h1>
                    <span @click="pageData.modal.show = false;" class="cursor-pointer">
                        X
                    </span>
                </div>
            </div>
            <div class="row p-4">
                <form @submit.prevent="handleSubmit">
                    <div class="mb-4">
                        <label for="title" class="block text-gray-700 font-bold mb-2">Title:</label>
                        <input v-model="pageData.modal.todo.title" type="text" id="title" name="title"
                            class="border rounded w-full py-2 px-3" required>
                    </div>
                    <div class="mb-4">
                        <label for="description" class="block text-gray-700 font-bold mb-2">Description:</label>
                        <textarea v-model="pageData.modal.todo.description" id="description" name="description"
                            class="border rounded w-full py-2 px-3" required></textarea>
                    </div>
                    <div class="mb-4">
                        <label for="status" class="block text-gray-700 font-bold mb-2">Status:</label>
                        <select v-model="pageData.modal.todo.status" id="status" name="status"
                            class="border rounded w-full py-2 px-3" required>

                            <option :value="key" v-for="(value, key) in statusToText">{{ value }}</option>
                        </select>
                    </div>

                    <div class="flex justify-center items-center mt-8 mb-8" v-if="pageData.loading">
                        <div class="loader"></div>
                    </div>
                    <div class="flex justify-end" v-else>
                        <button @click="handleSubmit" class="bg-blue-500 text-white px-4 py-2 rounded-full">Save</button>
                    </div>
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
