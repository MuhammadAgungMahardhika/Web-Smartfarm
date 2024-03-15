<?php

namespace App\Http\Controllers;

use App\Events\NotificationSent;
use Illuminate\Support\Facades\Auth;
use App\Models\DataKandang;
use App\Models\Kandang;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Repositories\DataKandangRepository;
use App\Repositories\DataKematianRepository;
use App\Repositories\KandangRepository;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;

class DataKandangController extends Controller
{

	protected $kandangRepository;
	protected $dataKandangRepository;
	protected $dataKematianRepository;
	/**
	 * Create a new controller instance.
	 */
	public function __construct(
		KandangRepository $kandangRepository,
		DataKandangRepository $dataKandangRepository,
		DataKematianRepository $dataKematianRepository,
	) {

		$this->kandangRepository = $kandangRepository;
		$this->dataKandangRepository = $dataKandangRepository;
		$this->dataKematianRepository = $dataKematianRepository;
	}

	public function index($id = null)
	{
		$dataKandang = new DataKandang();

		if ($id != null) {
			$items = $dataKandang::with(['kandang', 'data_kematians'])->find($id);
		} else {

			$items = $dataKandang->get();
		}
		return response(['data' => $items, 'status' => 200]);
	}

	public function getNextDay($idKandang)
	{
		$items = DB::table('data_kandang')
			->join('kandang', 'kandang.id', '=', 'data_kandang.id_kandang')
			->where('data_kandang.id_kandang', $idKandang)
			->orderBy('data_kandang.id', 'DESC')
			->first();
		// check apakah nilai sudah pernah ada
		if ($items) {
			$day = $items->hari_ke;
			$nextDay = $day + 1;
			$status = $items->status;

			// jika statusnya nonaktif maka atur ulang penomoran dari 1 dan update jadi aktif kembali
			if ($status == "nonaktif") {
				$nextDay = 1;
			}
		} else {
			$nextDay = 1;
		}

		$response = [
			'nextDay' => $nextDay,
		];
		return response(['data' => $response, 'status' => 200]);
	}

	public function getDataKandangByIdKandang($id)
	{

		$items =  DB::table('data_kandang')
			->join('kandang', 'kandang.id', '=', 'data_kandang.id_kandang')
			->leftJoin('data_kematian', 'data_kematian.id_data_kandang', '=', 'data_kandang.id')
			->select('data_kandang.*', 'kandang.nama_kandang', 'kandang.alamat_kandang', 'kandang.populasi_awal', 'kandang.luas_kandang', DB::raw('COALESCE(SUM(data_kematian.jumlah_kematian), 0) as total_kematian'), DB::raw('GROUP_CONCAT(data_kematian.jam SEPARATOR ",") AS jam_kematian'))
			->groupBy('data_kandang.id', 'data_kandang.id_kandang', 'data_kandang.hari_ke', 'data_kandang.pakan', 'data_kandang.minum', 'data_kandang.riwayat_populasi', 'data_kandang.date', 'data_kandang.classification', 'data_kandang.created_at', 'data_kandang.created_by', 'data_kandang.updated_at', 'data_kandang.updated_by', 'kandang.nama_kandang', 'kandang.alamat_kandang', 'kandang.populasi_awal', 'kandang.luas_kandang')
			->where('data_kandang.id_kandang', '=', $id)
			->orderBy('data_kandang.created_at', 'ASC')
			->get();
		return response(['data' => $items, 'status' => 200]);
	}
	// Filter by Date
	public function getDataKandangByDate(Request $request)
	{
		$idKandang = $request->id_kandang;
		$from = $request->from;
		$to = $request->to;

		$items =  DB::table('data_kandang')
			->join('kandang', 'kandang.id', '=', 'data_kandang.id_kandang')
			->leftJoin('data_kematian', 'data_kematian.id_data_kandang', '=', 'data_kandang.id')
			->select('data_kandang.*', 'kandang.nama_kandang', 'kandang.alamat_kandang', 'kandang.populasi_awal', 'kandang.luas_kandang', DB::raw('COALESCE(SUM(data_kematian.jumlah_kematian), 0) as total_kematian'))
			->groupBy('data_kandang.id', 'data_kandang.id_kandang', 'data_kandang.hari_ke', 'data_kandang.pakan', 'data_kandang.minum', 'data_kandang.riwayat_populasi', 'data_kandang.date', 'data_kandang.classification', 'data_kandang.created_at', 'data_kandang.created_by', 'data_kandang.updated_at', 'data_kandang.updated_by', 'kandang.nama_kandang', 'kandang.alamat_kandang', 'kandang.populasi_awal', 'kandang.luas_kandang')
			->where('data_kandang.id_kandang', '=', $idKandang)
			->where(function ($query) use ($from, $to) {
				$query->whereRaw('data_kandang.date >= ? AND data_kandang.date <= ?', [$from, $to]);
			})
			->orderBy('data_kandang.created_at', 'ASC')
			->get();
		return response(['data' => $items, 'status' => 200]);
	}

	// Filter By Classification
	public function getDataKandangByClassification(Request $request)
	{
		$idKandang = $request->id_kandang;
		$classification = $request->classification;

		$items =  DB::table('data_kandang')
			->join('kandang', 'kandang.id', '=', 'data_kandang.id_kandang')
			->leftJoin('data_kematian', 'data_kematian.id_data_kandang', '=', 'data_kandang.id')
			->select('data_kandang.*', 'kandang.nama_kandang', 'kandang.alamat_kandang', 'kandang.populasi_awal', 'kandang.luas_kandang', DB::raw('COALESCE(SUM(data_kematian.jumlah_kematian), 0) as total_kematian'))
			->groupBy('data_kandang.id', 'data_kandang.id_kandang', 'data_kandang.hari_ke', 'data_kandang.pakan', 'data_kandang.minum', 'data_kandang.riwayat_populasi', 'data_kandang.date', 'data_kandang.classification', 'data_kandang.created_at', 'data_kandang.created_by', 'data_kandang.updated_at', 'data_kandang.updated_by', 'kandang.nama_kandang', 'kandang.alamat_kandang', 'kandang.populasi_awal', 'kandang.luas_kandang')
			->where('data_kandang.id_kandang', '=', $idKandang)
			->where('data_kandang.classification', '=', $classification)
			->orderBy('data_kandang.created_at', 'ASC')
			->get();
		return response(['data' => $items, 'status' => 200]);
	}

	// Filter By Day
	public function getDataKandangByDay(Request $request)
	{
		$idKandang = $request->id_kandang;
		$day = $request->day;

		$items =  DB::table('data_kandang')
			->join('kandang', 'kandang.id', '=', 'data_kandang.id_kandang')
			->leftJoin('data_kematian', 'data_kematian.id_data_kandang', '=', 'data_kandang.id')
			->select('data_kandang.*', 'kandang.nama_kandang', 'kandang.alamat_kandang', 'kandang.populasi_awal', 'kandang.luas_kandang', DB::raw('COALESCE(SUM(data_kematian.jumlah_kematian), 0) as total_kematian'))
			->groupBy('data_kandang.id', 'data_kandang.id_kandang', 'data_kandang.hari_ke', 'data_kandang.pakan', 'data_kandang.minum', 'data_kandang.riwayat_populasi', 'data_kandang.date', 'data_kandang.classification', 'data_kandang.created_at', 'data_kandang.created_by', 'data_kandang.updated_at', 'data_kandang.updated_by', 'kandang.nama_kandang', 'kandang.alamat_kandang', 'kandang.populasi_awal', 'kandang.luas_kandang')
			->where('data_kandang.id_kandang', '=', $idKandang)
			->where('data_kandang.hari_ke', '=', $day)
			->orderBy('data_kandang.created_at', 'ASC')
			->get();
		return response(['data' => $items, 'status' => 200]);
	}

	public function getDetailKandangByIdKandang($id)
	{
		$items = Kandang::with('data_kandangs')->where('id', $id)->get();

		return response(['data' => $items, 'status' => 200]);
	}
	public function getJumlahKematianByDataKandangId($id)
	{
		$items = DB::table('data_kematian')->where('data_kematian.id_data_kandang', '=', $id)->select(DB::raw('COALESCE(sum(data_kematian.jumlah_kematian),0) as total_kematian'))->first();
		return response(['data' => $items, 'status' => 200]);
	}

	public function sendNotificationAlertToFarmer(Request $request)
	{
		$message = $request->message;
		// cari id  peternak  dari kandang
		$items = Kandang::get();
		$currentDate = date("Y-m-d");
		foreach ($items as $index => $value) {
			$idKandang = $value["id"];
			$namaKandang = $value["nama_kandang"];
			$idPeternak = 	$value["id_peternak"];
			// check apakah data kandang hari ini sudah diisi
			$isFilled = DataKandang::where('id_kandang', $idKandang)->where("date", $currentDate)->exists();
			if ($isFilled) {
				// Jikah sudah, kirim notifikasi ke telegram , bahwa terimakasih sudah menginputkan data hari ini
				$thanksMessage = "Thanks for submiting the daily input at ($currentDate)";
				Event(new NotificationSent($idKandang, $idPeternak, $thanksMessage . ". for kandang : ($namaKandang)"));
			} else {
				// Jika belum, kirim notifikasi ke telegram, bahwa hari ini belum mengimputkan data harian 
				Event(new NotificationSent($idKandang, $idPeternak, $message . ". for kandang : ($namaKandang)"));
			}
		}
		return response(['data' => $isFilled, 'status' => 200]);
	}

	public function store(Request $request)
	{
		$request->validate([
			'id_kandang' => 'required',
			'hari_ke' => 'required',
			'pakan' => 'required',
			'minum' => 'required',
			'riwayat_populasi' => 'required',
			'date' => 'required'
		]);

		DB::beginTransaction();
		try {
			$idKandang = $request->id_kandang;
			$riwayatPopulasi = $request->riwayat_populasi;
			$dataKematian = $request->data_kematian;
			$klasifikasi = count($dataKematian) > 0 ? "abnormal" : "normal";
			$dataKandang = $this->dataKandangRepository->createDataKandang(
				(object) [
					"id_kandang" => $idKandang,
					"hari_ke" => $request->hari_ke,
					"pakan" => $request->pakan,
					"minum" => $request->minum,
					"riwayat_populasi" => $riwayatPopulasi,
					"classification" => $klasifikasi,
					"date" => $request->date,
					"created_at" => Carbon::now()->timezone('Asia/Jakarta'),
					"created_by" => Auth::user()->id,
				]
			);
			$countJumlahKematian = 0;
			if (count($dataKematian) > 0) {
				for ($i = 0; $i < count($dataKematian); $i++) {
					$countJumlahKematian +=  $dataKematian[$i]['jumlah_kematian'];
					$jamKematian = $dataKematian[$i]['jam'];
					$jumlahKematian = $dataKematian[$i]['jumlah_kematian'];

					$this->dataKematianRepository->createDataKematian(
						(object)[
							"id_data_kandang" => $dataKandang->id,
							"jumlah_kematian" => $jumlahKematian,
							"jam" => $jamKematian,
							"created_at" => Carbon::now()->timezone('Asia/Jakarta'),
							"created_by" => Auth::user()->id
						]
					);
				}
			}

			$this->kandangRepository->changeKandangPopulationAndSetActiveStatus($idKandang, (object)[
				"populasi_saat_ini" => intval($riwayatPopulasi)
			]);

			// Kirim notifikasi jika klasifikasi abnormal atau ada data kematian kepada pemilik kandang
			if ($klasifikasi == "abnormal") {
				$kandang = Kandang::findOrFail($idKandang)->first();
				$namaKandang = $kandang->nama_kandang;
				$userId = $kandang->id_user; //pemilik kandang
				Event(new NotificationSent($idKandang, $userId, "New death data found in the ($namaKandang) farm house. Total: $countJumlahKematian death found."));
			}

			// Kirim notifikasi jika sudah berhasil input

			// $date = $request->date;
			// $thanksMessage = "Thanks for submiting the daily input at ( $date )";
			// $idPeternak =  Auth::user()->id;
			// Event(new NotificationSent($idKandang, 1, $thanksMessage . ". for kandang : ($namaKandang)"));

			DB::commit();
			return response()->json([
				'message' => 'success created data kandang',
				'dataKandang' => $dataKandang,
			], Response::HTTP_CREATED);
		} catch (ValidationException $e) {
			DB::rollBack();
			return response()->json([
				'message' => 'Validation Error',
				'errors' => $e->errors()
			], 422);
		} catch (QueryException $th) {
			DB::rollBack();
			return response()->json([
				'message' => $th->getMessage(),
			], 500);
		}
	}

	public function update(Request $request, $id)
	{
		$request->validate([
			'id_kandang' => 'required',
			'hari_ke' => 'required',
			'pakan' => 'required',
			'minum' => 'required',
			'riwayat_populasi' => 'required',
			'date' => 'required'
		]);

		DB::beginTransaction();
		try {

			$idKandang = $request->id_kandang;
			$riwayatPopulasi = $request->riwayat_populasi;
			$dataKematian = $request->data_kematian;
			$jumlahKematian = count($dataKematian);
			$updatedAt = Carbon::now()->timezone('Asia/Jakarta');
			$updatedBy = Auth::user()->id;
			$klasifikasi = $jumlahKematian > 0 ? "abnormal" : "normal";
			$dataKandang = $this->dataKandangRepository->editDataKandang(
				$id,
				(object) [
					"id_kandang" => $idKandang,
					"hari_ke" => $request->hari_ke,
					"pakan" => $request->pakan,
					"minum" => $request->minum,
					"riwayat_populasi" => $riwayatPopulasi,
					"classification" => $klasifikasi,
					"date" => $request->date,
					"updated_at" => $updatedAt,
					"updated_by" => $updatedBy,
				]
			);

			// delete data kematian
			$this->dataKematianRepository->deleteDataKematianByDataKandangId($id);
			// insert data kematian baru
			$countJumlahKematian = 0;
			if ($jumlahKematian > 0) {
				for ($i = 0; $i < $jumlahKematian; $i++) {
					$countJumlahKematian += $dataKematian[$i]['jumlah_kematian'];
					$this->dataKematianRepository->createDataKematian(
						(object)[
							"id_data_kandang" => $dataKandang->id,
							"jumlah_kematian" => $dataKematian[$i]['jumlah_kematian'],
							"jam" => $dataKematian[$i]['jam'],
							"created_at" => $updatedAt,
							"created_by" => $updatedBy
						]
					);
				}
			}

			// Ubah nilai populasi saat ini
			$this->kandangRepository->changeKandangPopulationAndSetActiveStatus($idKandang, (object)[
				"populasi_saat_ini" => intval($riwayatPopulasi)
			]);

			// Kirim notifikasi jika klasifikasi abnormal atau ada data kematian kepada pemilik kandang
			if ($klasifikasi == "abnormal") {
				$kandang = DB::table('kandang')
					->select('nama_kandang', 'id_user')
					->where('id', $idKandang)
					->first();
				$namaKandang = optional($kandang)->nama_kandang;
				$userId = optional($kandang)->id_user;
				Event(new NotificationSent($idKandang, $userId, "New death data found in the ($namaKandang) farm house. Total: $countJumlahKematian death found."));
			}

			DB::commit();
			return response()->json([
				'message' => 'success updated data kandang',
				'dataKandang' => $dataKandang
			], Response::HTTP_OK);
		} catch (ValidationException $e) {
			DB::rollBack();
			return response()->json([
				'message' => 'Validation Error',
				'errors' => $e->errors()
			], 422);
		} catch (QueryException $th) {
			DB::rollBack();
			return response()->json([
				'message' => $th->getMessage(),
			], 500);
		}
	}

	public function delete($id)
	{
		DB::beginTransaction();
		try {
			$dataKematian = DB::table('data_kematian')->where('data_kematian.id_data_kandang', '=', $id)
				->select(DB::raw('Sum(jumlah_kematian) as total_kematian'))
				->first();

			$jumlahKematian =  $dataKematian->total_kematian;

			$dataKandang = $this->dataKandangRepository->deleteDataKandang($id);
			$idKandang = $dataKandang->id_kandang;
			$populasiSaatIni = DB::table('kandang')->where('kandang.id', '=', $idKandang)->select('kandang.populasi_saat_ini')->first()->populasi_saat_ini;
			$populasiAkhir = intval($populasiSaatIni)  + intval($jumlahKematian);

			$this->kandangRepository->changeKandangPopulationAndSetActiveStatus($idKandang, (object)[
				"populasi_saat_ini" => $populasiAkhir
			]);
			DB::commit();
			return response()->json([
				'message' => 'success delete data kandang',
				'dataKandang' => $dataKandang
			], Response::HTTP_OK);
		} catch (QueryException $th) {
			DB::rollBack();
			return response()->json([
				'message' => $th->getMessage(),
			], 500);
		}
	}
}
