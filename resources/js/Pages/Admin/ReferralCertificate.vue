<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { computed, ref, watch } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import { defineProps } from 'vue';
import { jsPDF } from "jspdf";
import html2canvas from "html2canvas";



const props = defineProps({
  certificate: {
    type: Object,
    default: () => ({
      date: '',
      graduate: '',
      job_title: '',
      company: '',
      company_address: '',
      photo: ''
    })
  }
});

console.log('Graduate ID:', props.certificate.graduate);


function exportCertificateAsPDF() {
  const element = document.querySelector('.certificate-print-area');
  html2canvas(element, { scale: 2 }).then(canvas => {
    const imgData = canvas.toDataURL('image/png');
    const pdf = new jsPDF({
      orientation: "portrait",
      unit: "mm",
      format: "a4"
    });
    // Calculate width/height for A4
    const pageWidth = pdf.internal.pageSize.getWidth();
    const pageHeight = pdf.internal.pageSize.getHeight();
    const imgProps = pdf.getImageProperties(imgData);
    const pdfWidth = pageWidth;
    const pdfHeight = (imgProps.height * pdfWidth) / imgProps.width;

    pdf.addImage(imgData, 'PNG', 0, 0, pdfWidth, pdfHeight);
    pdf.save("referral-certificate.pdf");

    const pdfBlob = pdf.output('blob');
    const formData = new FormData();
    formData.append('certificate', pdfBlob, 'referral-certificate.pdf');
    formData.append('graduate_id', props.certificate.graduate_id); // Pass graduate/user id

    fetch(route('certificate.store'), {
      method: 'POST',
      body: formData,
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      },  
      credentials: 'same-origin'
    });

  });
}

</script>

<template>
  <AppLayout title="Manage Job Referrals">
    <div class="bg-white min-h-screen flex flex-col items-center justify-center py-12">
      <div class="certificate-print-area w-full max-w-2xl border rounded-lg shadow-lg p-8">
        <div class="w-full max-w-2xl border rounded-lg shadow-lg p-8">
          <div class="flex justify-between items-center mb-6">
            <div>
              <img src="/path/to/city-logo.png" alt="City Logo" class="h-16 mb-2" />
              <div class="text-xs text-gray-500">{{ certificate.date }}</div>
            </div>
            <div>
              <img :src="certificate.photo" alt="Applicant Photo" class="h-20 w-20 object-cover rounded" />
            </div>
          </div>
          <h2 class="text-xl font-bold text-center mb-2">OFFICE OF THE CITY MAYOR</h2>
          <h3 class="text-lg font-semibold text-center mb-4">Public Employment Service Office<br />General Santos City
          </h3>
          <div class="mb-6">
            <div class="font-semibold">THE PERSONNEL MANAGER</div>
            <div class="font-bold">{{ certificate.company }}</div>
            <div>{{ certificate.company_address }}</div>
          </div>
          <div class="mb-6">
            <p>Dear Sir/Madam:</p>
            <p class="mt-2">
              This office has arranged for Mr./Ms. <span class="font-bold underline">{{ certificate.graduate }}</span>
              to call on you regarding your opening for a <span class="font-bold underline">{{ certificate.job_title
              }}</span>.
            </p>
            <p class="mt-2">
              We would appreciate it very much if you would let us know the status of application of the said
              applicant.<br>
              Thank you.
            </p>
          </div>
          <div class="mt-8">
            <div class="font-bold">Very Truly Yours,</div>
            <div class="font-bold mt-2">LORELIE GERONIMO PACQUIAO</div>
            <div class="text-sm">CITY MAYOR</div>
            <div class="mt-4 text-sm">BY THE AUTHORITY OF THE CITY MAYOR</div>
            <div class="font-bold mt-2">NURHASSAN S. MANGONDATO</div>
            <div class="text-sm">SUPERVISING LABOR AND EMPLOYMENT OFFICER<br>DIVISION CHIEF, PESO GENSAN</div>
          </div>
          <div class="mt-8 text-xs text-gray-500 text-center">
            4th floor General Santos City Investment Action Center, City Hall Drive, General Santos City, 9500
            Philippines<br>
            (083) 553-3479 | peso_gensan@yahoo.com
          </div>
        </div>
      </div>



    </div>




  </AppLayout>

  <div class="flex justify-end max-w-2xl mx-auto mb-4 print:hidden">
    <button @click="exportCertificateAsPDF"
      class="px-4 py-2 bg-blue-600 text-white rounded shadow hover:bg-blue-700 transition">
      Print / Export as PDF
    </button>
  </div>
</template>


<style scoped>
@media print {

  /* Hide everything except the certificate */
  body,
  html {
    background: #fff !important;
    margin: 0 !important;
    padding: 0 !important;
    width: 210mm !important;
    height: 297mm !important;
  }

  body * {
    visibility: hidden !important;
  }

  .certificate-print-area,
  .certificate-print-area * {
    visibility: visible !important;
  }

  .certificate-print-area {
    position: absolute !important;
    left: 0 !important;
    top: 0 !important;
    width: 210mm !important;
    min-height: 297mm !important;
    max-width: 210mm !important;
    background: #fff !important;
    box-shadow: none !important;
    border: none !important;
    margin: 0 !important;
    padding: 24mm 18mm !important;
    /* A4 printable area */
    overflow: hidden !important;
  }

  /* Hide the print button */
  .print\:hidden {
    display: none !important;
  }

  /* Hide AppLayout and its children */
  #app>*:not(.certificate-print-area) {
    display: none !important;
  }
}
</style>