<template>
    <ion-page>
        <ion-content>
            <ion-grid class="page" fixed>
                <ion-row class="ion-justify-content-center">
                    <ion-col size="12" size-lg="6">
                        <ion-card v-if="competition">
                            <CompetitionFormHeader />
                            <ion-card-content>
                                <ion-list v-if="participants.length" ref="participantList">
                                    <ion-reorder-group @ionItemReorder="reorderParticipants($event)" :disabled="loading">
                                        <ion-item-sliding v-for="participant in participants" :key="participant.id">
                                            <ion-item>
                                                <ion-label>
                                                    {{ participant.order }}. {{ getParticipantUserProp('name', participant) }}
                                                    <div><small>{{ getCountryName(getParticipantUserProp('country', participant)) }}, {{ getParticipantUserProp('club', participant).name }}</small></div>
                                                </ion-label>
                                                <ion-reorder slot="end"></ion-reorder>
                                            </ion-item>
                                            <ion-item-options>
                                                <ion-item-option @click="removeParticipant(participant)">Ukloni</ion-item-option>
                                            </ion-item-options>
                                        </ion-item-sliding>
                                    </ion-reorder-group>
                                </ion-list>
                                <ion-label v-else class="empty">Nema dodanih natjecatelja.</ion-label>
                            </ion-card-content>
                        </ion-card>
                    </ion-col>
                </ion-row>
            </ion-grid>

            <ion-modal :is-open="isAddModalOpen" @didDismiss="setAddModal(false)">
                <ion-page>
                    <ion-header>
                        <ion-toolbar>
                            <ion-title>Dodaj natjecatelja</ion-title>
                            <ion-buttons slot="end">
                                <ion-button @click="setAddModal(false)">
                                    <ion-icon :icon="closeOutline"></ion-icon>
                                </ion-button>
                            </ion-buttons>
                        </ion-toolbar>
                    </ion-header>
                    <ion-content>
                        <ion-button expand="block" @click="fileInput.click()" class="import">Uvezi iz Airtribune tablice</ion-button>
                        <ion-searchbar placeholder="Traži natjecatelje..." @ionChange="onUserSearch"></ion-searchbar>
                        <ion-list v-if="searchUsers.length">
                            <ion-item v-for="user in searchUsers" :key="user.id" :color="isUserSelected(user) ? 'primary' : ''" button @click="clickUser(user)" detail="false">
                                <ion-label>
                                    {{ user.name }}
                                    <div><small>{{ getCountryName(user.country) }}, {{ user.club.name }}</small></div>
                                </ion-label>
                                <ion-icon slot="end" :icon="isUserSelected(user) ? removeOutline : addOutline"></ion-icon>
                            </ion-item>
                        </ion-list>

                        <ion-fab v-if="selectedUsers.length" vertical="bottom" horizontal="end" slot="fixed">
                            <ion-fab-button @click="addUsersToParticipants">
                                <ion-icon :icon="checkmarkOutline"></ion-icon>
                            </ion-fab-button>
                        </ion-fab>
                    </ion-content>
                </ion-page>
            </ion-modal>
        </ion-content>
        <CompetitionFormNavigation @click-add="setAddModal(true)" @click-continue="submitForm" :loading="loading" />

        <input ref="fileInput" @change="onFileChange" type="file" accept=".xls,.xlsx" v-show="false" />
    </ion-page>
</template>

<script>
import { IonPage, IonContent, IonGrid, IonRow, IonCol, IonCard, IonCardContent, IonModal, IonHeader, IonToolbar, IonButtons, IonIcon, IonTitle, IonSearchbar, IonList, IonItemSliding, IonItemOptions, IonItemOption, IonItem, IonLabel, IonFab, IonFabButton, IonReorder, IonReorderGroup, IonButton, onIonViewWillEnter, alertController, loadingController, toastController, isPlatform } from '@ionic/vue';
import { closeOutline, addOutline, removeOutline, checkmarkOutline } from 'ionicons/icons';
import { ref, computed } from 'vue';
import { useRouter, useRoute, onBeforeRouteLeave } from 'vue-router';
import equal from 'fast-deep-equal';
import store from '@/store';
import { getCompetition, updateCompetitionParticipants, updateCompetitionParticipantsFromXls } from '@/http/competition';
import { competitionNotFoundOffline } from '@/helpers/competition';
import { getCountryName } from '@/helpers/misc';
import CompetitionFormHeader from '@/components/CompetitionFormHeader';
import CompetitionFormNavigation from '@/components/CompetitionFormNavigation';

export default {
    name: 'CompetitionParticipantsFormPage',
    components: { IonPage, IonContent, IonGrid, IonRow, IonCol, IonCard, IonCardContent, IonModal, IonHeader, IonToolbar, IonButtons, IonIcon, IonTitle, IonSearchbar, IonItemSliding, IonItemOptions, IonItemOption, IonList, IonItem, IonLabel, IonFab, IonFabButton, IonReorder, IonReorderGroup, IonButton, CompetitionFormHeader, CompetitionFormNavigation },
    setup() {
        const router = useRouter();
        const route = useRoute();

        const loading = ref(false);
        const id = parseInt(route.params.id);
        const competition = ref();
        const participants = ref([]);
        const allUsers = ref([]);
        const searchableUsers = ref([]);
        const participantList = ref();
        const fileInput = ref();
        let originalParticipants = null;

        // Učitaj podatke natjecanja sa servera po IDu iz URLa.
        const fetchCompetition = async () => {
            if(isNaN(id)) return competitionNotFoundOffline();

            competition.value = await getCompetition(id) || await competitionNotFoundOffline();
            loading.value = false;
            
            // Dodaj postojeće natjecatelje.
            if(competition.value.participants.length) {
                selectedUsers.value = competition.value.participants.map(p => p.user_id);
                addUsersToParticipants();
            }

            originalParticipants = participants.value.map(o => ({ ...o }));
        };

        // Učitaj korisnike ako nisu prisutni u storeu.
        const fetchAllUsers = async () => {
            if(allUsers.value.length > 0) return;

            const users = computed(() => store.getters['users/users']);
            if(!users.value) await store.dispatch('users/fetch');

            for(const user of Object.values(users.value)) {
                allUsers.value.push(user);
                searchableUsers.value.push(user);
            }
        };

        onIonViewWillEnter(() => {
            loading.value = true;
            fetchAllUsers().then(() => {
                fetchCompetition();
            });
        });

        // Traži korisnike koji već nisu dodani po imenu ili državi.
        const userSearchQuery = ref('');
        const onUserSearch = (event) => userSearchQuery.value = event.detail.value || '';
        const searchUsers = computed(() => {
            if(userSearchQuery.value) {
                return searchableUsers.value.filter(user => {
                    return userSearchQuery.value.toLowerCase().split(' ').every(v => user.name.toLowerCase().includes(v) || user.country.toLowerCase().includes(v));
                });
            } else {
                return searchableUsers.value;
            }
        });

        const selectedUsers = ref([]);
        const selectUser = (user) => selectedUsers.value.push(user.id);
        const deselectUser = (user) => selectedUsers.value = selectedUsers.value.filter(id => id !== user.id);
        const isUserSelected = (user) => selectedUsers.value.includes(user.id);
        const clickUser = (user) => isUserSelected(user) ? deselectUser(user) : selectUser(user);

        const isAddModalOpen = ref(false);
        const setAddModal = (state) => isAddModalOpen.value = state;

        // Dodaj odabrane natjecatelje.
        const addUsersToParticipants = () => {
            for(const userId of selectedUsers.value) {
                // Pronađi podatke korisnika.
                const user = allUsers.value.find(u => u.id == userId);
                if(user) {
                    // Dodaj korisnika u natjecatelje.
                    const participant = {
                        user_id: userId,
                        club_id: user.club_id,
                        order: participants.value.length + 1
                    };
                    participants.value.push(participant);
                }
            }

            // Ukloni korisnika iz popisa korisnika koji se mogu pretraživati.
            searchableUsers.value = searchableUsers.value.filter(u => !selectedUsers.value.includes(u.id));
            // Ukloni novo dodane korisnike iz popisa odabranih korisnika.
            selectedUsers.value = [];
            // Zatvori modal.
            isAddModalOpen.value = false;
        };

        // Pošalji podatke serveru.
        const submitForm = async () => {
            // Pošalji podatke serveru samo ako ima promjena.
            if(!equal(participants.value, originalParticipants)) {
                loading.value = true;
                await updateCompetitionParticipants(id, { participants: participants.value });
                loading.value = false;
            }

            router.push({ name: 'competition-form-results', params: { id } });

            // Resetiraj natjecanje, stanje učitavanja i popis natjecatelja.
            competition.value = undefined;
            participants.value = [];
            loading.value = false;
        };

        // Pronađi ime/klub/državu korisnika iz popisa svih korisnika po IDu.
        const getParticipantUserProp = (prop, participant) => {
            const user = allUsers.value.find(u => u.id == participant.user_id);
            return user ? user[prop] : '';
        };

        // Sortiraj natjecatelje ovisno o korisnikovoj promjeni redoslijeda.
        const reorderParticipants = (event) => {
            participants.value = event.detail.complete(participants.value);

            let i = 1;
            for(const [index] of participants.value.entries()) {
                participants.value[index].order = i;
                i++;
            }
        };

        // Ukloni natjecatelja.
        const removeParticipant = (participant) => {
            // Zatvori slider s opcijom uklanjanja.
            participantList.value.$el.closeSlidingItems();

            participants.value = participants.value.filter(p => p.user_id !== participant.user_id);

            // Pronađi korisnika iz popisa svih korisnika po IDu i dodaj ga u popis korisnika koji se mogu pretraživati.
            const user = allUsers.value.find(u => u.id == participant.user_id);
            searchableUsers.value.push(user);
        };

        // Uvezi natjecatelje iz Airtribune tablice.
        const onFileChange = async (event) => {
            if(event.target.files.length) {
                const file = event.target.files[0];

                // Zahtijevaj dodatnu potvrdu korisnika.
                const alert = await alertController.create({
                    header: 'Uvoz iz tablice',
                    message: `Jeste li sigurni da želite uvesti natjecatelje iz "${file.name}"?`,
                    buttons: [
                        {
                            text: 'Odustani',
                            role: 'cancel',
                            cssClass: 'secondary'
                        },
                        {
                            text: 'Nastavi',
                            role: 'confirm'
                        },
                    ]
                });

                await alert.present();
                const { role } = await alert.onDidDismiss();

                // Resetiraj input ako korisnik odustane.
                if(role != 'confirm') {
                    return fileInput.value.value = '';
                }

                // Pošalji tablicu serveru.
                const formData = new FormData();
                formData.append('file', file);
                
                const loader = await loadingController.create({ message: 'Učitavanje...'  });
                await loader.present();

                const response = await updateCompetitionParticipantsFromXls(competition.value.id, formData);

                // Resetiraj input.
                fileInput.value.value = '';

                // Prikaži poruku sa servera ako je uvoz neuspješan.
                if(!response.status) {
                    loader.dismiss();
                    return toastController.create({ message: response.message, duration: 3000 }).then(t => t.present());
                }

                // Resetiraj popis natjecatelja i ponovo učitaj natjecanje sa servera.
                participants.value = [];
                await fetchCompetition();
                loader.dismiss();
            }
        };

        // Zatvori modal za dodavanje natjecatelja u browseru ako je pritisnut back button (Ionic to automatski rješava na Android aplikaciji).
        onBeforeRouteLeave(async (_to, _from, next) => {
            if((isPlatform('pwa') || isPlatform('mobileweb')) && isAddModalOpen.value) {
                isAddModalOpen.value = false;
                next(false);
            } else {
                next();
            }
        });

        return {
            competition,
            selectedUsers,
            clickUser,
            isUserSelected,
            searchUsers,
            onUserSearch,
            addUsersToParticipants,
            isAddModalOpen,
            setAddModal,
            participants,
            submitForm,
            getParticipantUserProp,
            reorderParticipants,
            removeParticipant,
            participantList,
            fileInput,
            onFileChange,
            getCountryName,
            loading,
            closeOutline,
            addOutline,
            removeOutline,
            checkmarkOutline
        }
    }
}
</script>

<style lang="scss" scoped>
ion-content {
    --padding-bottom: 52px;
}

ion-grid, ion-row {
    height: 100%;
}

ion-card {
    height: calc(100% - 52px);
}

ion-card-content {
    height: calc(100% - 60px);
    padding-top: 20px;

    ion-label.empty {
        // lol
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
    }
}

ion-item {
    --padding-start: 0;
    --padding-end: 0;
    --inner-padding-start: 15px;
    --inner-padding-end: 0;
}

ion-button.import {
    margin: 12px 12px 0 12px;
}
</style>