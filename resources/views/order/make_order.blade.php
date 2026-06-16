@extends('/layouts/main')

@push('css-dependencies')
<link rel="stylesheet" type="text/css" href="/css/order.css" />
@endpush

@push('scripts-dependencies')
<script src="/js/make_order.js"></script>
<script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>
@endpush

@push('modals-dependencies')
@endpush

@section('content')
<div class="container-fluid px-2 px-lg-4">
  <h1 class="main-title">
    {{ $title }}
  </h1>
  <div class="row">

    <!-- Left -->
    <div class="col-12 col-lg-9">
      <div class="accordion" id="accordionMain">

        <!-- top field -->
        <div class="accordion-item mb-3 px-4 py-3">
          <form action="/order/make_order/{{ $product->id }}" method="post" enctype="multipart/form-data">
            @csrf

            <!-- hidden input -->
            <input type="hidden" name="product_id" value="{{ old('product_id', $product->id) }}">

            <div class="row mb-3">
              <div class="col-md-8">
                <div class="form-group">
                  <label for="product_name">Product Name</label>
                  <input id="product_name" name="product_name" value="{{ $product->product_name }}" type="text"
                    class="form-control" disabled>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label for="price">Price per pieces</label>
                  @if ($product->discount == 0)
                  <input type="hidden" id="price" name="price" data-truePrice="{{ old('price', $product->price) }}"
                    value="Rp.
                {{ old('price', $product->price) }}" type="text" class="form-control" disabled>
                  @else
                  <input type="hidden" id="price" name="price"
                    data-truePrice="{{ old('price', ((100 - $product->discount)/100) * $product->price) }}"
                    value="Rp. {{ old('price', ((100 - $product->discount)/100) *$product->price) }}" type="text"
                    class="form-control" disabled>
                  @endif
                  <div class="input-group" style="display:unset;">
                    <div class="input-group-prepend">
                      @if ($product->discount == 0)
                      <span class="input-group-text">
                        {{ $product->price }}
                      </span>
                      @else
                      <span class="input-group-text">Rp. {{ ((100 - $product->discount)/100) * $product->price }} <span
                          class="strikethrough ms-4">
                          {{ $product->price }}
                        </span><sup><sub class="mx-1">of</sub>
                          {{ $product->discount }}%
                        </sup>
                      </span>
                      @endif
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="form-group col-2">
              <label for="quantity">Quantity</label>
              <input id="quantity" name="quantity" data-productId="{{ $product->id }}"
                value="{{ old('quantity', '0' ) }}" type="number" min="0"
                class="form-control @error('quantity') is-invalid @enderror" onchange="myCounter()">
            </div>
            <div class="mb-3 col-12">
              @error('quantity')
              <div class="text-danger">{{ $message }}</div>
              @enderror
            </div>
            <div class="row mb-3">
              <div class="col-12">Destination</div>
              <div class="form-group col-7">
                <input type="text" class="form-control  @error('province') is-invalid @enderror" id="province" name="province" placeholder="Enter Province" value="{{ old('province', '') }}">
                @error('province')
                <div class="text-danger">{{ $message }}</div>
                @enderror
              </div>
              <div class="form-group col-5">
                <input type="text" class="form-control  @error('city') is-invalid @enderror" id="city" name="city" placeholder="Enter City" value="{{ old('city', '') }}">
                @error('city')
                <div class="text-danger">{{ $message }}</div>
                @enderror
              </div>
            </div>
            <div class="form-group mb-3">
              <label for="address">Address Detail</label>
              <input type="hidden" name="shipping_address" id="shipping_address">
              <input id="address" name="address" type="text" class="form-control @error('address') is-invalid @enderror"
                value="{{ old('address', auth()->user()->address) }}">
              @error('address')
              <div class="text-danger">{{ $message }}</div>
              @enderror
            </div>
        </div>

        <!-- Transfer QR -->
        <div class="accordion-item mb-3 ">
          <h2 class="h5 px-4 py-3 accordion-header d-flex justify-content-between align-items-center">
            <div class="form-check w-100 collapsed">
              <input class="form-check-input" type="radio" name="payment_method" id="transfer_qr"
                data-bs-toggle="collapse" data-bs-target="#collapseCC" aria-expanded="false" value="1" {{
                old('payment_method')=='1' ? 'checked' : '' }} onclick="hideMessage('qr')">
              <label class="form-check-label pt-1" for="transfer_qr" data-bs-toggle="collapse"
                data-bs-target="#collapseCC" aria-expanded="false" onclick="hideMessage('qr')">
                Transfer QR
              </label>
              @error('payment_method')
              <div class="text-danger" id="qr_alert">{{ $message }}</div>
              @enderror
            </div>
            <span>
              <button type="button" class="btn btn-sm btn-primary" id="btn_upload_proof_makeorder" onclick="document.getElementById('proof_payment_file').click()">
                <i class="fa fa-fw fa-cloud-upload"></i> Upload Bukti
              </button>
              <input type="file" id="proof_payment_file" name="proof_payment" accept="image/*" style="display: none;" onchange="previewProofPayment(this)">
            </span>
          </h2>
          <div id="collapseCC" class="accordion-collapse collapse {{ old('payment_method')==1 ? 'show' : '' }}"
            data-bs-parent="#accordionMain">
            <div class="accordion-body">
              <div class="text-center">
                <h6>Scan Kode QR Berikut</h6>
                <img src="{{ asset('storage/qr-code.png') }}" alt="QR Code Payment" width="300px" class="my-3 rounded">
                <p class="text-muted small">Tunjukkan QR code di atas ke kasir atau scan dengan aplikasi pembayaran Anda</p>
              </div>
              <div class="mt-3 p-3 bg-light rounded">
                <p class="small mb-0">
                  <strong>Instruksi:</strong><br>
                  1. Scan QR code di atas menggunakan aplikasi pembayaran Anda<br>
                  2. Masukkan jumlah pembayaran: <strong>Rp. <span id="total_price_qr">0</span></strong><br>
                  3. Selesaikan pembayaran<br>
                  4. Upload bukti pembayaran
                </p>
              </div>
              
              <!-- Upload Bukti Pembayaran Section -->
              <div class="mt-4 p-3 border rounded bg-white">
                <h6 class="mb-3">📸 Upload Bukti Pembayaran</h6>
                <div class="text-center">
                  <div id="proof_preview_container" style="display: none; margin-bottom: 15px;">
                    <img id="proof_preview_image" src="" alt="Preview" width="200px" class="rounded border">
                    <p class="mt-2 small text-muted" id="proof_file_name"></p>
                    <button type="button" class="btn btn-sm btn-warning" onclick="clearProofPreview()">
                      <i class="fa fa-fw fa-trash"></i> Hapus & Pilih Ulang
                    </button>
                  </div>
                  <div id="proof_empty_container">
                    <p class="text-muted small mb-2">Belum ada bukti pembayaran</p>
                    <button type="button" class="btn btn-primary btn-sm" onclick="document.getElementById('proof_payment_file').click()">
                      <i class="fa fa-fw fa-image"></i> Pilih Gambar
                    </button>
                    <p class="text-muted small mt-2">Max file size: 2MB (PNG, JPG, GIF)</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        {{-- COD --}}
        <div class="accordion-item mb-3 border">
          <h2 class="h5 px-4 py-3 accordion-header d-flex justify-content-between align-items-center">
            <div class="form-check w-100 collapsed">
              <input class="form-check-input" type="radio" name="payment_method" id="cod" data-bs-toggle="collapse"
                data-bs-target="#collapsePP" aria-expanded="false" value="2" {{ old('payment_method')=='2' ? 'checked'
                : '' }} onclick="hideMessage('cod')">
              <label class="form-check-label pt-1" for="cod" data-bs-toggle="collapse" data-bs-target="#collapsePP"
                aria-expanded="false" onclick="hideMessage('cod')">
                Cash on Delivery
              </label>
              @error('payment_method')
              <div class="text-danger" id="cod_alert">{{ $message }}</div>
              @enderror
            </div>
            <span>
              <img src="{{ asset('storage/icons/cash-on-delivery.png') }}" height="50px" alt="logo COD" />
            </span>
          </h2>
          <div id="collapsePP" class="accordion-collapse collapse  {{ old('payment_method')==2 ? 'show' : '' }}"
            data-bs-parent="#accordionMain">
            <div class="accordion-body">
              <div class="content-cod">
                <div class="note-cod">Note: use this method if you want to do COD transactions</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- Right -->
    <div class="col-12 col-lg-3">
      <div class="card position-sticky top-0">
        <div class="p-3 bg-light bg-opacity-10">
          <h6 class="card-title mb-3">Order Summary</h6>
          {{-- loading --}}
          <div id="loading_transaction" style="display: none">
            <lottie-player src="https://assets8.lottiefiles.com/packages/lf20_raiw2hpe.json" background="transparent"
              speed="1" style="width: auto; height: 125px;" loop autoplay>
            </lottie-player>
          </div>
          {{-- transaction resume --}}
          <div id="transaction">
            <div class="d-flex justify-content-between mb-1 small">
              <span>Subtotal</span> <span><span>Rp. </span> <span id="sub-total">0</span></span>
            </div>
            <div class="d-flex justify-content-between mb-1 small">
              <span>Delivery</span><span>
                <span>Rp. </span><span id="shipping" data-shippingCost="0">0</span>
              </span>
            </div>

            <input type="hidden" name="coupon_used" id="coupon_used" value="0">

            <div class="d-flex justify-content-between mb-1 small">
              <span>Coupon
                @if (auth()->user()->coupon == 0)
                (no coupon)
                @else
                <span class="align-items-center">
                  <label for="use_coupon" style="cursor:pointer">(use coupon</label>
                </span>
                <span>
                  <input id="use_coupon" type="checkbox" onchange="changeStatesCoupon()">
                </span>
                )
                @endif
              </span><span><span></span><span id="coupon" data-valueCoupon="{{ auth()->user()->coupon }}">
                  {{ auth()->user()->coupon }} Coupon
                </span></span>
            </div>
            @if (auth()->user()->coupon != 0)
            <div class="d-flex justify-content-between mb-1 small text-danger">
              <span>Coupon used</span> <span><span id="couponUsedShow">0 coupon</span></span>
            </div>
            @endif
          </div>
          <hr>
          <div class="d-flex justify-content-between mb-4 small">
            <span>TOTAL</span> <strong class="text-dark"><span>Rp. </span><span id="total">0</span></strong>
            <input type="hidden" name="total_price" id="total_price" value="{{ old('total_price', '0') }}">
          </div>
          <div class="form-group small mb-3">
            Make sure you really understand the order you make. If you want to get more information please contact <a
              class="link-danger"
              href="https://wa.me/6281230451084?text=Saya%20ingin%20menanyakan%20detail%20terkait%20produk%20anda"
              target="_blank" style="text-decoration: none;">@admin</a>
          </div>
          <button type="submit" class="btn btn-primary w-100 mt-2">Submit</button>
        </div>
      </div>
      </form>
    </div>
  </div>
</div>
@endsection