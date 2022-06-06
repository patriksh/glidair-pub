<template>
    <ion-toolbar>
        <ion-searchbar @ionChange="onSearch" :placeholder="placeholder"></ion-searchbar>
        <ion-buttons slot="end">
            <ion-button @click="openSortingPicker">
                <ion-icon :icon="filterOutline"></ion-icon>
            </ion-button>
        </ion-buttons>
    </ion-toolbar>
</template>

<script>
import { IonToolbar, IonSearchbar, IonButtons, IonButton, IonIcon, pickerController } from '@ionic/vue';
import { filterOutline } from 'ionicons/icons';
import { reactive, watch } from 'vue';

export default {
    name: 'SearchbarWithSorting',
    components: { IonToolbar, IonSearchbar, IonButtons, IonButton, IonIcon },
    props: ['placeholder'],
    emits: ['change'],
    setup(_props, { emit }) {
        const params = reactive({
            keyword: '',
            orderColumn: 'created_at',
            orderType: 'desc'
        });

        // Pri svakoj promjeni filtera obavijesti parent komponentu kako bi se učitali novi podaci.
        watch(params, () => emit('change', params));

        const onSearch = (event) => params.keyword = event.detail.value || '';

        // Prikaži izbornik za sortiranje.
        const openSortingPicker = async () => {
            const pickerOptions = [
                { text: 'Prvo najnoviji', value: { orderColumn: 'created_at', orderType: 'desc'} },
                { text: 'Prvo najstariji', value: { orderColumn: 'created_at', orderType: 'asc'} },
                { text: 'Ime (A-Ž)', value: { orderColumn: 'name', orderType: 'desc'} },
                { text: 'Ime (Ž-A)', value: { orderColumn: 'name', orderType: 'asc'} },
            ];

            let selectedIndex = null;

            // Označi odabranu opciju.
            pickerOptions.map((o, index) => {
                if(o.value.orderColumn == params.orderColumn && o.value.orderType == params.orderType) {
                    selectedIndex = index;
                }

                return o;
            });

            const picker = await pickerController.create({
                columns: [{
                    name: 'sort',
                    options: pickerOptions,
                    selectedIndex
                }],
                buttons: [
                    {
                        text: 'Odustani',
                        role: 'cancel',
                    },
                    {
                        text: 'Potvrdi',
                        handler: (value) => {
                            params.orderColumn = value.sort.value.orderColumn;
                            params.orderType = value.sort.value.orderType;
                        },
                    },
                ],
            });

            await picker.present();
        };

        return {
            onSearch,
            openSortingPicker,
            filterOutline
        }
    }
}
</script>

<style lang="scss" scoped>
ion-toolbar {
    --background: none;
    padding-top: 15px;

    ion-buttons {
        padding-inline-end: 12px;
    }
}
</style>