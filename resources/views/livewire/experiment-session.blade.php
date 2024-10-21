<div class="" style="width: 100%; height: 100vh;" x-data="konvaApp()">
    <!-- Modal for participant information -->
    <div x-show="showParticipantModal" class="fixed inset-0 flex items-center justify-center z-30">
        <div class="fixed inset-0 backdrop-blur-lg" aria-hidden="true"></div>
        <div class="bg-white p-8 rounded shadow-md w-1/3 z-50">
            <h2 class="text-2xl font-bold mb-4">Participant Information</h2>
            <div class="mt-4">
                <label for="participant_name" class="block text-sm font-medium text-gray-700">Name</label>
                <input type="text" required wire:model="participant_name" id="participant_name"
                    class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                @error('participant_name')
                    <span class="text-red-500">{{ $message }}</span>
                @enderror
            </div>
            <div class="mt-4">
                <label for="participant_email" class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" wire:model="participant_email" id="participant_email" required
                    class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                @error('participant_email')
                    <span class="text-red-500">{{ $message }}</span>
                @enderror
            </div>
            <div class="flex justify-end">
                <button @click="saveParticipant" class="bg-blue-500 text-white px-4 py-2 rounded">Start</button>
            </div>
        </div>
    </div>

    <div id="konva-container" class="grow h-[90%]"></div>
    <div class="flex justify-center">
        <button @click="finalize" x-show="!finalized"
            class="z-10 px-5 py-2.5 bg-green-500 rounded-md text-lg font-bold">Terminé</button>
    </div>

    <!-- Modal for finalizing groups -->
    <div x-show="showGroupModal" class="fixed inset-0 flex items-center justify-center z-50">
        <div class="bg-white backdrop-blur-md p-8 rounded shadow-md w-2/3 max-h-[90vh] overflow-y-auto">
            <div x-show="currentStep === 1">
                <h2 class="text-2xl font-bold mb-4">Finalize Groups</h2>
                <div class="grid grid-cols-2 gap-4">
                    <template x-for="(group, index) in groups" :key="index">
                        <div class="border p-4 rounded">
                            <div class="flex justify-between items-center mb-2">
                                <div class="flex-grow mr-2">
                                    <label class="block mb-1">Group <span x-text="index + 1"></span>:</label>
                                    <input type="text" x-model="group.name" class="w-full p-2 border rounded">
                                </div>
                                <div>
                                    <label :for="'color-' + index" class="block text-sm font-medium mb-1">Color</label>
                                    <input type="color" x-model="group.color" :id="'color-' + index"
                                        class="p-1 h-10 w-14 block bg-white border border-gray-200 cursor-pointer rounded-lg">
                                </div>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold mb-2">Media in this group:</h3>
                                <ul class="grid grid-cols-3 gap-2">
                                    <template x-for="media in group.elements" :key="media.id">
                                        <li class="mb-2">
                                            <template x-if="media.type === 'image'">
                                                <img :src="media.url" alt="Image"
                                                    class="w-full h-24 object-cover rounded">
                                            </template>
                                            <template x-if="media.type === 'sound'">
                                                <audio controls class="w-full">
                                                    <source :src="media.url" type="audio/mpeg">
                                                    Your browser does not support the audio element.
                                                </audio>
                                            </template>
                                        </li>
                                    </template>
                                </ul>
                            </div>
                        </div>
                    </template>
                </div>
                <div class="flex justify-between mt-4">
                    <span>Total Time: <span x-text="formattedElapsedTime"></span></span>
                    <button @click="saveGroupData" class="bg-blue-500 text-white px-4 py-2 rounded">Save</button>
                </div>
            </div>
            <div x-show="currentStep === 2">
                <div class="text-center">
                    <h2 class="text-2xl font-bold mb-4">Expérimentation terminée</h2>
                    <p class="mb-4">Merci d'avoir participé à cette expérimentation.</p>
                    <p>Vous pouvez maintenant fermer cette fenêtre.</p>
                    {{-- <button @click="showGroupModal = false"
                        class="mt-4 bg-green-500 text-white px-4 py-2 rounded">Fermer</button> --}}
                </div>
            </div>
        </div>
    </div>
</div>

@script
    <script>
        Alpine.data('konvaApp', () => ({
            width: window.innerWidth,
            height: window.innerHeight,
            audioPlaying: false,
            elementsMovable: true,
            thresholdCluster: 150,
            finalized: false,
            media: @json($media),
            stage: null,
            layer: null,
            clusterLayer: null,
            actionsLog: [],
            startTime: null,
            elapsedTime: 0,
            showParticipantModal: true,
            participantName: '',
            participantEmail: '',
            showGroupModal: false,
            groups: [],
            currentStep: 1,

            init() {
                this.stage = new Konva.Stage({
                    container: 'konva-container',
                    width: this.width,
                    height: this.height,
                });

                this.layer = new Konva.Layer();
                this.clusterLayer = new Konva.Layer();
                this.stage.add(this.layer);
                this.stage.add(this.clusterLayer);

                this.loadMedia();
            },

            loadMedia() {
                this.layer.destroyChildren();
                this.media.forEach((item, index) => {
                    if (item.type === 'image') {
                        const imageObj = new Image();
                        imageObj.onload = () => {
                            const image = new Konva.Image({
                                x: item.x,
                                y: item.y,
                                image: imageObj,
                                width: item.button_size,
                                height: item.button_size,
                                draggable: true
                            });
                            image.id(item.id);
                            image.type = 'image';
                            image.url = item.url;
                            this.layer.add(image);
                            this.layer.draw();
                        };
                        imageObj.src = item.url;
                    } else if (item.type === 'sound') {
                        const audioElement = new Audio(item.url);

                        var group = new Konva.Group({
                            x: item.x,
                            y: item.y,
                            draggable: this.elementsMovable,
                        });

                        const rect = new Konva.Rect({
                            width: item.button_size,
                            height: item.button_size,
                            fill: item.button_color,
                        });

                        const text = new Konva.Text({
                            x: rect.width() / 2,
                            y: rect.height() / 2,
                            text: index + 1,
                            fontSize: 20,
                            fontFamily: "Calibri",
                            fill: "white",
                            align: "center",
                        });

                        text.offsetX(text.width() / 2);
                        text.offsetY(text.height() / 2);

                        group.add(rect);
                        group.add(text);
                        group.id(item.id);
                        group.type = 'sound';
                        group.url = item.url;
                        this.layer.add(group);
                        this.layer.draw();

                        group.on("dragend", () => {
                            const logEntry = {
                                id: item.id,
                                x: group.x(),
                                y: group.y(),
                                timestamp: Date.now()
                            };
                            this.actionsLog.push(logEntry);
                            console.log(
                                `Audio ${item.id} déplacé à x: ${group.x()}, y: ${group.y()}`
                            );
                        });

                        group.on("click", () => {
                            if (!this.audioPlaying) {
                                this.audioPlaying = true;
                                audioElement.play();
                                console.log(`Audio ${item.id} est en train de jouer.`);
                                audioElement.onended = () => {
                                    this.audioPlaying = false;
                                    console.log(`Audio ${item.id} a fini de jouer.`);
                                };
                            } else {
                                console.log("Un autre fichier audio est déjà en lecture.");
                            }
                        });
                    }
                });
            },

            finalize() {
                this.elementsMovable = false;
                this.audioPlaying = false;
                this.stage.find("Group").forEach(group => {
                    group.draggable(false);
                });
                this.createClusters();
                this.finalized = true;
                this.endExperiment();
                this.currentStep = 1;
                this.showGroupModal = true;
            },

            undo() {
                this.elementsMovable = true;
                this.audioPlaying = true;
                this.stage.find("Group").forEach(group => {
                    group.draggable(true);
                });
                this.clusterLayer.destroyChildren();
                this.clusterLayer.draw();
                this.finalized = false;
            },

            createClusters() {
                var threshold = this.thresholdCluster;
                var groups = this.stage.find("Group");
                var clusters = [];

                groups.forEach((group) => {
                    var foundCluster = false;
                    for (var cluster of clusters) {
                        if (cluster.some((g) => this.getDistance(g.position(), group.position()) <
                                threshold)) {
                            cluster.push(group);
                            foundCluster = true;
                            break;
                        }
                    }
                    if (!foundCluster) {
                        clusters.push([group]);
                    }
                });

                var colors = ["#FF0000", "#00FF00", "#0000FF", "#FFFF00", "#FF00FF"];

                this.groups = clusters.map((cluster, index) => ({
                    id: index + 1,
                    name: `Group ${index + 1}`,
                    color: colors[index % colors.length],
                    elements: cluster.map(g => ({
                        id: g.id(),
                        type: g.type,
                        url: g.url,
                        x: g.x(),
                        y: g.y()
                    }))
                }));


                clusters.forEach((cluster, index) => {
                    var minX = Math.min(...cluster.map((g) => g.x()));
                    var maxX = Math.max(...cluster.map((g) => g.x() + 50));
                    var minY = Math.min(...cluster.map((g) => g.y()));
                    var maxY = Math.max(...cluster.map((g) => g.y() + 50));
                    var circle = new Konva.Circle({
                        x: (minX + maxX) / 2,
                        y: (minY + maxY) / 2,
                        radius: Math.max(maxX - minX, maxY - minY) / 2 + 20,
                        stroke: this.groups[index].color,
                        strokeWidth: 5,
                        opacity: 1,
                    });
                    this.clusterLayer.add(circle);
                });
                this.clusterLayer.draw();
            },

            getDistance(pos1, pos2) {
                var dx = pos1.x - pos2.x;
                var dy = pos1.y - pos2.y;
                return Math.sqrt(dx * dx + dy * dy);
            },

            startExperiment() {
                this.startTime = Date.now();
            },

            endExperiment() {
                if (this.startTime) {
                    this.elapsedTime += Date.now() - this.startTime;
                    this.startTime = null;
                }
            },

            saveParticipant() {
                this.showParticipantModal = false;
                this.startExperiment();
            },

            saveGroupData() {
                const groupsData = this.groups.map(group => ({
                    id: group.id,
                    name: group.name,
                    color: group.color,
                    elements: group.elements
                }));

                // Call Livewire method to save data
                @this.call('saveExperimentData', groupsData, this.actionsLog, this.elapsedTime);
                this.currentStep = 2;
            },

            get formattedElapsedTime() {
                const totalSeconds = Math.floor(this.elapsedTime / 1000);
                const minutes = Math.floor(totalSeconds / 60);
                const seconds = totalSeconds % 60;
                return `${minutes}m ${seconds}s`;
            }
        }));
    </script>
@endscript
