<?php

namespace App\Http\Controllers\API;

/**
 * @OA\Info(
 *      version="1.0",
 *      title="Документация по API Arenatickets для партнеров",
 *      @OA\Contact(
 *          email="hello@spaceduck.kz"
 *      ),
 *      @OA\License(
 *          name="Apache 2.0",
 *          url="http://www.apache.org/licenses/LICENSE-2.0.html"
 *      ),
 *     description="С каждым запросом должно передаваться три параметра в заголовке запроса. <br/><ul><li><code>TIMESTAMP</code> - временная метка запроса. Формат: UNIX timestamp (в секундах, без миллисекунд)</li><li><code>X-PARTNER-TOKEN</code> - токен партнера. Предоставляется менеджером Arenatickets.</li><li><code>SIGNATURE</code> - подпись запроса. Формируется следующим образом: менеджером Topbilet партнеру предоставляется public key. При каждом запросе формируется строка, состоящая из соединенных значений пути запроса (без basepath, пример ниже) и TIMESTAMP (то же значение, что и передается отдельно в заголовке). Потом эта строка подписывается public key и кодируется в base64. Получившееся значение передается в SIGNATURE</li></ul><p><b>Пример запроса</b></p><p>Предположим, мы хотим получить список событий. Мы получаем от Topbilet токен (для примера '1a2b3c4d5e6f7g8h9i') и публичный ключ. Получаем текущий unix timestamp (предположим, сейчас 1661959289). Путь для получения списка мероприятий '/partner/shows' (не забывайте про слэш вначале). Соединяем это в единую строку, получаем <b>'/partner/shows1661959289'</b>. Подписываем эту строку, используя полученный public key. Получившееся значени кодируем в base64 (btoa в Javascript, base64_encode в PHP, Base64.getEncoder().encodeToString в Java...), получаем значение, что-то вроде 'TOJNMhGRszz0iVraRqQZx2pVTAf7yLUISxtHl...', только длиннее. В итоге, заголовки запроса будут следующими: <ul><li><code>X-PARTNER-TOKEN</code>: <b>1a2b3c4d5e6f7g8h9i</b></li><li><code>TIMESTAMP</code>: <b>1661959289</b></li><li><code>SIGNATURE</code>: <b>TOJNMhGRszz0iVraRqQZx2pVTAf7yLUISxtHl...</b></li></ul></p>"
 * )
 *
 * @OA\Server(
 *      url=L5_SWAGGER_CONST_HOST_DEVEL,
 *      description="Develop API Server"
 * )
 * @OA\Server(
 *      url=L5_SWAGGER_CONST_HOST,
 *      description="Production API Server"
 * )
 *
 * @OA\Schema(
 *  schema="Timetable",
 *  title="Событие",
 * 	@OA\Property(property="id", type="integer", example=1),
 * 	@OA\Property(property="date", type="string", example="2022-09-10 19:30:00"),
 * 	@OA\Property(property="title", ref="#/components/schemas/Translation"),
 * 	@OA\Property(property="type", type="string", example="sections", description="Бывает два типа: sections или pricegroups, по секторам или по ценовым группам"),
 * 	@OA\Property(property="venue_scheme_id", type="integer", example=1),
 * 	@OA\Property(property="venue", ref="#/components/schemas/Venue"),
 * )
 *
 * @OA\Schema(
 *  schema="Order",
 *  title="Заказ",
 * 	@OA\Property(property="id", type="integer", example=1),
 * 	@OA\Property(property="original_price", type="integer", example=100),
 * 	@OA\Property(property="fee", type="integer", example=10),
 * 	@OA\Property(property="discount", type="integer", example=0),
 * 	@OA\Property(property="final_price", type="integer", example=110),
 * 	@OA\Property(property="paid", type="boolean", example=false, description="Оплачен ли заказ"),
 * 	@OA\Property(property="sent", type="boolean", example=false, description="Отправлен ли билет по почте"),
 * 	@OA\Property(property="created_at", type="string", example="2022-09-01 12:30:00"),
 * 	@OA\Property(property="items", type="array", @OA\Items(ref="#/components/schemas/OrderItem")),
 * )
 *
 * @OA\Schema(
 *  schema="Translation",
 *  title="Мультиязычное название",
 * 	@OA\Property(property="kz", type="string", example="Қазақша аты"),
 * 	@OA\Property(property="ru", type="string", example="Название на русском"),
 * 	@OA\Property(property="en", type="string", example="English title"),
 * )
 *
 * @OA\Schema(
 *  schema="Venue",
 *  title="Площадка",
 * 	@OA\Property(property="id", type="integer", example=1),
 * 	@OA\Property(property="title", ref="#/components/schemas/Translation"),
 * )
 *
 *
 * @OA\Schema(
 *  schema="SectionInfo",
 *  title="Общая информация по сектору / ценовой группе",
 * 	@OA\Property(property="amount", type="integer", example=1),
 * 	@OA\Property(property="type", type="string", example="sections", description="Бывает два типа: sections или pricegroups, по секторам или по ценовым группам"),
 * 	@OA\Property(property="pricegroup", ref="#/components/schemas/Pricegroup", description="в случае, если type = pricegroups"),
 * 	@OA\Property(property="section", ref="#/components/schemas/Section", description="в случае, если type = sections"),
 * )
 *
 * @OA\Schema(
 *  schema="Pricegroup",
 *  title="Ценовая группа",
 * 	@OA\Property(property="id", type="integer", example=1),
 * 	@OA\Property(property="title", ref="#/components/schemas/Translation"),
 * 	@OA\Property(property="price", type="integer", example=100),
 * )
 *
 * @OA\Schema(
 *  schema="Section",
 *  title="Сектор",
 * 	@OA\Property(property="id", type="integer", example=1),
 * 	@OA\Property(property="title", ref="#/components/schemas/Translation"),
 * )
 *
 *
 * @OA\Schema(
 *  schema="Seat",
 *  title="Место",
 * 	@OA\Property(property="id", type="integer", example=1),
 * 	@OA\Property(property="x", type="integer", example=100, description="x координата"),
 * 	@OA\Property(property="y", type="integer", example=100, description="y координата"),
 * 	@OA\Property(property="row", type="string", example="1", description="номер / название ряда"),
 * 	@OA\Property(property="seat", type="string", example="1", description="номер / название места"),
 * )
 *
 *
 * @OA\Schema(
 *  schema="Ticket",
 *  title="Билет",
 * 	@OA\Property(property="id", type="integer", example=1),
 *  @OA\Property(property="row", type="string", example="1", description="номер / название ряда"),
 * 	@OA\Property(property="seat", type="string", example="1", description="номер / название места"),
 *  @OA\Property(property="seat_id", type="integer", example=1),
 * 	@OA\Property(property="price", type="integer", example=100),
 * 	@OA\Property(property="blocked", type="boolean", example=false, description="Блокирован ли билет (те, забронирован, но не выкуплен)"),
 * 	@OA\Property(property="sold", type="boolean", example=false, description="Продан ли билет"),
 * )
 *
 * @OA\Schema(
 *  schema="CartItem",
 *  title="Билет корзины",
 * 	@OA\Property(property="ticket_id", type="integer", example=1, description="Id билета (именно билета, не места). Параметр нужен только при рассадке по местам (type = seats из запроса по сектору)"),
 * 	@OA\Property(property="section_id", type="integer", example=1, description="Id сектора. Параметр нужен только при входных по сектору без места (type = enter из запроса по сектору)"),
 * 	@OA\Property(property="pricegroup_id", type="integer", example=1, description="Id ценовой группы. Параметр нужен только при покупке билетов по ценовым группам (тип события - pricegroups)"),
 * )
 *
 * @OA\Schema(
 *  schema="OrderItem",
 *  title="Билет заказа",
 *  @OA\Property(property="seat_id", type="integer", example=1),
 * 	@OA\Property(property="price", type="integer", example=100),
 * 	@OA\Property(property="section_id", type="integer", example=1),
 * 	@OA\Property(property="ticket_id", type="integer", example=1),
 *  @OA\Property(property="row", type="string", example="1", description="номер / название ряда"),
 * 	@OA\Property(property="seat", type="string", example="1", description="номер / название места"),
 * 	@OA\Property(property="pricegroup_id", type="integer", example=1),
 * 	@OA\Property(property="barcode", type="string", example="1234567890", description="Штрихкод, только если заказ оплачен."),
 * )
 *
 *
 * @OA\Schema(
 *  schema="Error",
 *  title="Ошибка",
 * 	@OA\Property(property="error", type="string", example="Неверный формат данных"),
 * ),
 *
 * @OA\Schema(
 *  schema="NotFoundError",
 *  title="Ошибка",
 * 	@OA\Property(property="error", type="string", example="Запись не найдена"),
 * )

 */

class SwaggerDescriptionController {

}
