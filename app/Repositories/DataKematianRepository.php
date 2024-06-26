<?php

namespace App\Repositories;

use App\Models\DataKematian;
use Illuminate\Support\Facades\Log;
use stdClass;
use Exception;
use Illuminate\Support\Facades\DB;

class DataKematianRepository
{


  public function createDataKematian(object $data): DataKematian
  {
    try {
      $dataKematian = new DataKematian();
      $dataKematian->id_data_kandang = $data->id_data_kandang;
      $dataKematian->jumlah_kematian = $data->jumlah_kematian;
      $dataKematian->jam = $data->jam;
      $dataKematian->created_at = $data->created_at;
      $dataKematian->created_by = $data->created_by;
      $dataKematian->save();

      return $dataKematian;
    } catch (Exception $th) {
      Log::error('Error create data kematian.');
      Log::error($th->getMessage());
      throw $th;
    }
  }

  public function editDataKematian($id, object $data): DataKematian
  {
    try {
      $dataKematian = DataKematian::findOrFail($id);
      $dataKematian->id_data_kandang = $data->id_data_kandang;
      $dataKematian->kematian_terbaru = $data->kematian_terbaru;
      $dataKematian->jumlah_kematian = $data->jumlah_kematian;
      $dataKematian->jam = $data->jam;
      $dataKematian->hari = $data->hari;
      $dataKematian->updated_at = $data->updated_at;
      $dataKematian->updated_by = $data->updated_by;
      $dataKematian->save();

      return $dataKematian;
    } catch (Exception $th) {
      Log::error('Error update data kandang.');
      Log::error($th->getMessage());
      throw $th;
    }
  }

  public function deleteDataKematian($id): DataKematian
  {
    try {
      $dataKematian = DataKematian::findOrFail($id);
      $dataKematian->delete();

      return $dataKematian;
    } catch (Exception $th) {
      Log::error('Error delete data kandang.');
      Log::error($th->getMessage());
      throw $th;
    }
  }
  public function deleteDataKematianByDataKandangId($idDataKandang)
  {
    try {
      $dataKematian = DB::table('data_kematian')->where('id_data_kandang', $idDataKandang)->delete();
      return $dataKematian;
    } catch (Exception $th) {
      Log::error('Error delete data kandang.');
      Log::error($th->getMessage());
      throw $th;
    }
  }
}
