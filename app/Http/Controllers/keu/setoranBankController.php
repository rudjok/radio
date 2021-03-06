<?php

namespace App\Http\Controllers\keu;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Http\Models\keu\fa_setoran_bank_mst as ModelMst;
use App\Http\Models\keu\fa_setoran_bank_det as ModelDet;
use App\Http\Models\keu\fa_setoran_bank_jurnal_det as ModelJurnalDet;

use Carbon;
use DB;
use Terbilang;
use PDF;

class setoranBankController extends Controller
{
    
    public function _index(){
        return view('modules.keu.setoranBank');
    }

    private function generate_id(){
        $max_id = modelMst::where('no_bukti','like','SB'.date('Ym').'%')->max('no_bukti');
        return 'SB'.date('Ym').(!empty($max_id) ? str_pad(((int)substr($max_id, 8)+1),4,'0',STR_PAD_LEFT) : '0001'); 
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        if($request->get('search')){
            $items = modelMst::where("no_bukti", "LIKE", "%".$request->get('search')."%")
                     //->where("status_aktif","=","A")
                     ->paginate(5);      
        }else{
          //$items = modelMst::where("status_aktif","=","A")->paginate(5);
          $items = modelMst::paginate(5);
        }
        // dd($items);
        return response($items);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        //DB::enableQueryLog();
        $tgl_cetak      = \DateTime::createFromFormat('D M d Y H:i:s e+', $request->tgl_cetak);
        $from_periode   = \DateTime::createFromFormat('D M d Y H:i:s e+', $request->from_periode);
        $to_periode     = \DateTime::createFromFormat('D M d Y H:i:s e+', $request->to_periode);

        $no_bukti =  $this->generate_id();

        $request->offsetSet("no_bukti", $no_bukti);
        $request->offsetSet("tgl_cetak", $tgl_cetak);
        $request->offsetSet("from_periode", $from_periode);
        $request->offsetSet("to_periode", $to_periode);
        $request->offsetSet("no_bank", $request->no_bank);
        $request->offsetSet("terbilang", UcWords(Terbilang::make($request->jumlah, ' Rupiah')));
        $request->offsetSet("kelompok", $request->kelompok);
        $request->offsetSet("jumlah", $request->jumlah);
        $request->offsetSet("lks", 'JAG');
        $request->offsetSet("keterangan", $request->keterangan);
        $request->offsetSet("posting", 0);

        modelMst::create($request->except(['det','gl']));
        
        $this->insDetail($request, $no_bukti);

        //dd(DB::getQueryLog());
        //dd($request->all());
        //return modelMst::find($no_bukti);

        return $this->edit($no_bukti);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
        DB::enableQueryLog();
        $items = modelMst::with('fa_bank_mst','fa_setoran_bank_det.fa_penerimaan_det.fa_bank_mst','fa_setoran_bank_jurnal_det.acc_coas_mst','fa_setoran_bank_jurnal_det.acc_cost_center')->find($id);   
        return response($items);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
        //DB::enableQueryLog();
        $tgl_cetak = \DateTime::createFromFormat('D M d Y H:i:s e+', $request->tgl_cetak);
        $from_periode   = \DateTime::createFromFormat('D M d Y H:i:s e+', $request->from_periode);
        $to_periode     = \DateTime::createFromFormat('D M d Y H:i:s e+', $request->to_periode);

        $request->offsetSet("tgl_cetak", $tgl_cetak);
        $request->offsetSet("from_periode", $from_periode);
        $request->offsetSet("to_periode", $to_periode);
        $request->offsetSet("no_bank", $request->no_bank);
        $request->offsetSet("terbilang", UcWords(Terbilang::make($request->jumlah, ' Rupiah')));
        $request->offsetSet("kelompok", $request->kelompok);
        $request->offsetSet("jumlah", $request->jumlah);
        $request->offsetSet("keterangan", $request->keterangan);

        modelMst::find($id)->update($request->except(['det', 'gl']));
        // dd(DB::getQueryLog());
        
        $this->delDetail($id);
        $this->insDetail($request, $id);

        return $this->edit($id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function insDetail($request, $no_bukti){

        if($request->det){
            $det  = $request->det;
            foreach ($det as $key => $value) {
                $det[$key]['no_bukti']      = $no_bukti;
                $det[$key]['id_cust']       = '';
                $det[$key]['coa_id']        = '';
                $det[$key]['jatuh_tempo']   = \DateTime::createFromFormat('D M d Y H:i:s e+', $det[$key]['jatuh_tempo']);
                unset($det[$key]['$$hashKey']);
                unset($det[$key]['nama_bank']);
                unset($det[$key]['keterangan']);
                unset($det[$key]['fa_bank_mst']);
            }
            modelDet::insert($det);
        }

        if($request->jurnal_det){
            $jurnal_det  = $request->jurnal_det;
            foreach ($jurnal_det as $key => $value) {
                $jurnal_det[$key]['no_bukti']      = $no_bukti;
                unset($jurnal_det[$key]['$$hashKey']);
                unset($jurnal_det[$key]['coa_name']);
                unset($jurnal_det[$key]['cost_keterangan']);
            }
            modelJurnalDet::insert($jurnal_det);
        }    
        //print_r($jurnal_det);
    } 

    private function delDetail($id)
    {
        modelDet::where('no_bukti', $id)->delete();
        modelJurnalDet::where('no_bukti', $id)->delete();
    }   

    public function rptFormSetoranBank(Request $request){
        return view('modules/keu/form/frmRptSetoranBank');
    }

    public function rptSetoranBank(Request $request){
        //dd($request);
        $query = DB::table("BI_KEU.FA_SETORAN_BANK_MST A")
                    ->join("BI_KEU.FA_SETORAN_BANK_DET B", "a.no_bukti", "=", "b.no_bukti")
                    ->leftJoin("SYS_RADIO.PMS_CUSTOMER_MST D", "B.ID_CUST", "=", "D.ID_CUSTOMER")
                    ->join("BI_KEU.FA_BANK_MST E", "B.NO_BANK", "=", "E.NO")
                    ->select(
                       "A.NO_BUKTI",
                       "A.TGL_CETAK",
                       "TO_CHAR(A.TGL_CETAK,'DD/MM/YY HH:MM:SS') WAKTU_CETAK",
                       "E.COA_ID_BANK",
                       "E.NAMA_BANK",
                       "INITCAP(A.TERBILANG) TERBILANG",
                       "D.NAMA_CUSTOMER",
                       "B.NO_BANK",
                       "B.NO_SERI",
                       "B.COA_ID", 
                       "b.JATUH_TEMPO", 
                       "b.jumlah kredit"
                    );

        if($request->berdasarkan == "true"){
            $items = $query->whereBetween("a.no_bukti", [$request->bukti_awal, $request->bukti_akhir])->get();
            //echo $request->bukti_awal;
            //echo $request->bukti_akhir;
        }
        else {
            $items = $query->whereBetween("a.tgl_cetak", [$request->tgl_awal, $request->tgl_akhir])->get();
            //echo $request->tgl_awal;
            //echo $request->tgl_akhir;
        }
        
        $output = 'pdf';            
        if($output == 'pdf'){
            $pdf = PDF::loadView('modules.keu.report.notaSetoranBank', ['vData' => $items])->setPaper('a4');//->setOrientation('landscape');
            return $pdf->download('nota_setoran_bank.pdf');
        }
        else{
            Excel::create('Daftar Voucher', function($excel) use ($hasil) {
            $excel->sheet('Excel sheet', function($sheet) use ($hasil) {
                $sheet->loadView('modules.keu.report.nota_pembayaran')->with('vData', $hasil)->with('vDet', $det);
            });
            $excel->setTitle('Daftar Voucher');     
            })->export('xls'); 
        }
    }
}
