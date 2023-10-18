const divMonthImportUnit = [];
let descoMonthImportUnit = 0;

const divMonthSalesAdj = [];
let descoMonthSalesAdj = 0;

const divYtdImportUnit = [];
let descoYtdImportUnit = 0;
const divYtdSalesUnit = [];
let descoYtdSalesUnit = 0;

const divMonthlyCollection = [];
let descoMonthlyCollection = 0;

const divMthlyCollAdj = [];
let dscMthlyCollAdj = 0;

const divRowList = document.querySelectorAll("tr");
const firstDataRow = 3;

descoMonthImportUnit = parseFloat(divRowList[firstDataRow].children[2].children[0].value);
descoMonthSalesAdj = parseFloat(divRowList[firstDataRow].children[5].children[0].value) ? parseFloat(divRowList[firstDataRow].children[5].children[0].value) : 0;
descoYtdImportUnit = parseFloat(divRowList[firstDataRow].children[8].children[0].value);
descoYtdSalesUnit = parseFloat(divRowList[firstDataRow].children[10].children[0].value);

descoMonthlyCollection = parseFloat(divRowList[firstDataRow].children[15].textContent);
dscMthlyCollAdj = parseFloat(divRowList[firstDataRow].children[16].children[0].value) ? parseFloat(divRowList[firstDataRow].children[16].children[0].value) : 0;

//console.log("DESCO Import " + descoMonthImportUnit);
//console.log("DESCO Sales Adjust " + descoMonthSalesAdj);
// console.log("DESCO Monthly Collection " + descoMonthlyCollection);
// console.log("DESCO  Collection adjustment " + dscMthlyCollAdj);

const restDataRow = firstDataRow + 1;
for (let i = 0; i < 24; i++) {
  divMonthImportUnit[i] = parseFloat(divRowList[i + restDataRow].children[2].children[0].value);
  let divSlsAdjVal = parseFloat(divRowList[i + restDataRow].children[5].children[0].value);
  divMonthSalesAdj[i] = divSlsAdjVal ? divSlsAdjVal : 0;
  divYtdImportUnit[i] = parseFloat(divRowList[i + restDataRow].children[8].children[0].value);
  divYtdSalesUnit[i] = parseFloat(divRowList[i + restDataRow].children[10].children[0].value);

  divMthlyCollAdj[i] = parseFloat(divRowList[i + restDataRow].children[16].children[0].value) ? parseFloat(divRowList[i + restDataRow].children[16].children[0].value) : 0;
}

// console.log("All Division Import " + divMonthImportUnit);
// console.log("All Division Sales Adjust " + divMonthSalesAdj);
// console.log("All Division YTD Import " + divYtdImportUnit);
// console.log("All Division collection adjustment " + divMthlyCollAdj);

verifyAdj();

document.getElementById("modForm").onkeydown = function (e) {
  if (e.code == 13) {
    e.preventDefault();
  }
};

function verifyAdj() {
  let dataMatch = false;
  const trgElmnt = document.getElementById("chkingRow");

  // console.log("Match " + dataMatch);

  const totalMonthlyImport = divMonthImportUnit.reduce((previousValue, currentValue) => previousValue + currentValue, 0);
  // console.log(divMonthImportUnit);
  // console.log("Total Monthly Import Unit is: " + totalMonthlyImport);
  trgElmnt.children[1].innerHTML = totalMonthlyImport;
  if (totalMonthlyImport === descoMonthImportUnit) dataMatch = true;
  else dataMatch = false;

  // console.log("Match " + dataMatch);

  const totalMonthlySalesUnit = parseFloat(trgElmnt.children[2].innerHTML);

  const totalMonthlySalesAdj = divMonthSalesAdj.reduce((previousValue, currentValue) => previousValue + currentValue, 0);
  // console.log(divMonthSalesAdj);
  // console.log("Total Monthly Adjusted Sales Unit is: " + totalMonthlySalesAdj);
  trgElmnt.children[4].innerHTML = totalMonthlySalesAdj;
  if (dataMatch === true && totalMonthlySalesAdj === descoMonthSalesAdj) dataMatch = true;
  else dataMatch = false;

  // console.log("Match " + dataMatch);

  const monthlyAdjustedSysLoss = calculateSystemLoss(totalMonthlyImport, totalMonthlySalesUnit + totalMonthlySalesAdj);
  trgElmnt.children[5].innerHTML = monthlyAdjustedSysLoss.toFixed(2);

  const totalYtdImport = divYtdImportUnit.reduce((previousValue, currentValue) => previousValue + currentValue, 0);
  // console.log("Total YTD Import Unit is: " + totalYtdImport);

  const totalYtdSls = divYtdSalesUnit.reduce((previousValue, currentValue) => previousValue + currentValue, 0);
  // console.log("Total YTD Sales Unit is: " + actTotalYtdSls);

  trgElmnt.children[6].innerHTML = divRowList[firstDataRow].children[14].innerHTML;

  // console.log("FOcus start here");
  // console.log(actTotalYtdSls);
  // console.log(totalMonthlySalesAdj);

  // const adjTotalYtdSls = actTotalYtdSls + totalMonthlySalesAdj;

  // console.log(adjTotalYtdSls);
  // console.log("FOcus ends here");
  const adjYtdSysLoss = calculateSystemLoss(totalYtdImport, totalYtdSls);
  trgElmnt.children[7].innerHTML = adjYtdSysLoss.toFixed(2);

  // Collection Related Checking
  const totalMonthlyCollectionAdj = divMthlyCollAdj.reduce((previousValue, currentValue) => previousValue + currentValue, 0);
  //console.log("Total col adj " + totalMonthlyCollectionAdj);
  trgElmnt.children[12].innerHTML = totalMonthlyCollectionAdj;
  if (dataMatch == true && totalMonthlyCollectionAdj === dscMthlyCollAdj) {
    dataMatch = true;
  } else dataMatch = false;
  /////////////////////////////////////////////////

  if (dataMatch === true) trgElmnt.children[9].children[0].disabled = false;
  else trgElmnt.children[9].children[0].disabled = true;
}

function adjustCollection(trgElmnt) {
  // console.log(trgElmnt);
  const dvsnRowNo = Array.from(trgElmnt.parentNode.children).indexOf(trgElmnt);
  // console.log("Row no " + dvsnRowNo);
  if (dvsnRowNo == 2) {
    dscMthlyCollAdj = parseFloat(divRowList[3].children[16].children[0].value) ? parseFloat(divRowList[3].children[16].children[0].value) : 0;
  } else {
    divMthlyCollAdj[dvsnRowNo - 3] = parseFloat(divRowList[dvsnRowNo + 1].children[16].children[0].value) ? parseFloat(divRowList[dvsnRowNo + 1].children[16].children[0].value) : 0;
  }
  // console.log("COLL  ADJ " + divMthlyCollAdj[dvsnRowNo - 3]);
  verifyAdj();
  // console.log("Total col adj " + divMthlyCollAdj);
}

function storeImportSales(changedRow) {
  //console.log(changedRow);
  const dvsnRowNo = Array.from(changedRow.parentNode.children).indexOf(changedRow);
  // console.log("Row no is " + dvsnRowNo);
  if (dvsnRowNo == 2) {
    descoMonthImportUnit = parseFloat(divRowList[firstDataRow].children[2].children[0].value);
    descoMonthSalesAdj = parseFloat(divRowList[firstDataRow].children[5].children[0].value) ? parseFloat(divRowList[firstDataRow].children[5].children[0].value) : 0;
    descoYtdImportUnit = parseFloat(divRowList[firstDataRow].children[8].children[0].value);
    descoYtdSalesUnit = parseFloat(divRowList[firstDataRow].children[10].children[0].value);

    // console.log("DESCO Import " + descoMonthImportUnit);
    // console.log("DESCO Sales Adjust " + descoMonthSalesAdj);
    // console.log("DESCO YTD Import " + descoYtdImportUnit);
    // console.log("DESCO YTD Sales " + descoYtdSalesUnit);
  } else {
    let monthImport = parseFloat(changedRow.children[2].children[0].value);
    if (!monthImport) monthImport = parseFloat(changedRow.children[1].innerHTML);
    divMonthImportUnit[dvsnRowNo - 3] = monthImport;

    let mnthSalesAdjust = parseFloat(changedRow.children[5].children[0].value);
    if (!mnthSalesAdjust) mnthSalesAdjust = 0;
    divMonthSalesAdj[dvsnRowNo - 3] = mnthSalesAdjust;

    const ytdImport = parseFloat(changedRow.children[8].children[0].value);
    divYtdImportUnit[dvsnRowNo - 3] = ytdImport;

    const ytdSalesUnit = parseFloat(changedRow.children[10].children[0].value);
    divYtdSalesUnit[dvsnRowNo - 3] = ytdSalesUnit;
  }
}

function setYtdFields(changedRow) {
  //YTD import
  const prevMnthYtdImport = parseFloat(changedRow.children[7].innerHTML);
  console.log("prev month hidden YTD import " + prevMnthYtdImport);
  let currentMnthImport = parseFloat(changedRow.children[2].children[0].value);
  if (!currentMnthImport) currentMnthImport = parseFloat(changedRow.children[1].innerHTML);
  console.log("monthly import " + currentMnthImport);
  const ytdImportUnit = prevMnthYtdImport + currentMnthImport;
  console.log("YTD import " + ytdImportUnit);
  changedRow.children[8].children[0].value = ytdImportUnit.toFixed(0);

  //YTD  Sales Unit
  const prevMnthYtdSales = parseFloat(changedRow.children[9].innerHTML);
  console.log("Hidden YTD sales is: " + prevMnthYtdSales);
  const currentMnthActualSales = parseFloat(changedRow.children[3].innerHTML);
  console.log("monthly sales " + currentMnthActualSales);
  let salesAdjust = parseFloat(changedRow.children[5].children[0].value);
  if (!salesAdjust) salesAdjust = 0;
  let currentMnthsalesUnit = currentMnthActualSales + salesAdjust;
  const ytdSalesUnit = prevMnthYtdSales + currentMnthsalesUnit;
  console.log("ytd sales " + ytdSalesUnit);
  changedRow.children[10].children[0].value = ytdSalesUnit.toFixed(0);

  //YTD  System Loss
  let ytdSysLoss = calculateSystemLoss(ytdImportUnit, ytdSalesUnit);
  changedRow.children[13].children[0].value = ytdSysLoss.toFixed(2);
  console.log("Adjusted YTD Sys Loss " + ytdSysLoss);

  //YTD Sales Adjustment
  changedRow.children[14].innerHTML = parseFloat(changedRow.children[11].children[0].value) + salesAdjust;
}

function auditImportChng(trgElmnt) {
  let auditedImp = parseFloat(trgElmnt.children[2].children[0].value);
  // console.log("Audited Import is " + auditedImp);

  const actualSales = parseFloat(trgElmnt.children[3].innerHTML);
  let adjustedSales = parseFloat(trgElmnt.children[5].children[0].value);
  if (!adjustedSales) adjustedSales = 0;
  // console.log(" Sales adjust  " + adjustedSales);
  let salesUnit = actualSales + adjustedSales;

  const systemLoss = calculateSystemLoss(auditedImp, salesUnit);
  trgElmnt.children[6].children[0].value = systemLoss.toFixed(2);

  setYtdFields(trgElmnt);
  storeImportSales(trgElmnt);
  verifyAdj();
}

function adjustSales(trgElmnt) {
  const importUnit = parseFloat(trgElmnt.children[2].children[0].value);
  if (!importUnit) importUnit = parseFloat(trgElmnt.children[1].innerHTML);
  const actualSales = parseFloat(trgElmnt.children[3].innerHTML);
  // console.log("Import Unit from audited import field " + importUnit);
  // console.log("Sales Unit from sales field " + actualSales);

  let reqSlsAdj = parseFloat(trgElmnt.children[5].children[0].value);
  if (!reqSlsAdj) reqSlsAdj = 0;
  // console.log("Sales Adjust typed " + reqSlsAdj);

  let salesUnit = actualSales + reqSlsAdj;

  let reqSysLoss = calculateSystemLoss(importUnit, salesUnit);
  // console.log("Required Systoem loss " + reqSysLoss);
  trgElmnt.children[6].children[0].value = reqSysLoss.toFixed(2);

  setYtdFields(trgElmnt);
  storeImportSales(trgElmnt);
  verifyAdj();
}

function adjustSystemLoss(trgElmnt) {
  const importUnit = parseFloat(trgElmnt.children[2].children[0].value);
  const actualSales = parseFloat(trgElmnt.children[3].innerHTML);
  // console.log("Import Unit is " + importUnit);
  // console.log("Sales Unit from sales field " + actualSales);

  let reqSysLoss = parseFloat(trgElmnt.children[6].children[0].value);
  // console.log("Reqrd sys loss from field " + reqSysLoss);

  let requiredSales = importUnit - (reqSysLoss * importUnit) / 100;
  let salesAdj = Math.round(requiredSales - actualSales);
  trgElmnt.children[5].children[0].value = salesAdj;
  let salesUnit = actualSales + salesAdj;

  setYtdFields(trgElmnt);
  storeImportSales(trgElmnt);
  verifyAdj();
}

function calculateSystemLoss(importUnit, salesunit) {
  return ((importUnit - salesunit) / importUnit) * 100;
}
